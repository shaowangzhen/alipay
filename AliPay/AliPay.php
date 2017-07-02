<?php
namespace AliPay;

class AliPay
{
    private $aop;
    public function __construct()
    {
        // init ali pay commen api params
        $this->aop = new \AopClient ();
        if (Helper::isProduction()) { // todo 改写成自己的 正式或者测试环境的判断方法；
            $this->aop->gatewayUrl = trim(AliPayConfig::GATE_WAY_URL);
            $this->aop->appId = trim(AliPayConfig::APP_ID);
            $this->aop->rsaPrivateKey = trim(AliPayConfig::RSA_PRIVATE_KEY);
            $this->aop->alipayrsaPublicKey= trim(AliPayConfig::ALI_PAY_RSA_PUBLIC_KEY);
        } else {
            $this->aop->gatewayUrl = trim(AliPayConfig::GATE_WAY_URL_TEST);
            $this->aop->appId = trim(AliPayConfig::APP_ID_TEST);
            $this->aop->rsaPrivateKey = trim(AliPayConfig::RSA_PRIVATE_KEY_TEST);
            $this->aop->alipayrsaPublicKey= trim(AliPayConfig::ALI_PAY_RSA_PUBLIC_KEY_TEST);
        }
        $this->aop->apiVersion = trim(AliPayConfig::API_VERSION);
        $this->aop->signType = trim(AliPayConfig::SIGN_TYPE);
        $this->aop->postCharset = trim(AliPayConfig::POST_CHARSET) ;
        $this->aop->format = trim(AliPayConfig::FORMAT);
    }

    /*
     * params @$outBizNo MUST 商户转账唯一订单号(发起转账来源方定义的转账单据ID，用于将转账回执通知给来源方。只支持半角英文、数字，及“-”、“_”)
     * params @$payeeAccount MUST 收款方账户(支付宝登录号，支持邮箱和手机号格式,付款方和收款方不能是同一个账户。)
     * parama @$amount MUST 转账金额，单位：元,只支持2位小数，小数点前最大支持13位，金额必须大于等于0.1元。
     * params @$payerShowName OPTIONAL 付款方显示姓名（最长支持100个英文/50个汉字）。 如果不传，则默认显示该账户在支付宝登记的实名。收款方可见。
     * params @$payeeRealName OPTIONAL 收款方真实姓名（最长支持100个英文/50个汉字）。 如果本参数不为空，则会校验该账户在支付宝登记的实名是否与收款方真实姓名一致。
     * params @$remark  OPTIONAL 转账备注（支持200个英文/100个汉字）。 当付款方为企业账户，且转账金额达到（大于等于）50000元，remark不能为空。收款方可见，会展示在收款用户的账单中。
     *
     * if success return [
     *          'code' => 10000,
     *          'msg' => "Success"
     *          "order_id" => "20160627110070001502260006780837",
     *          "out_biz_no" => "3142321423432",
     *          "pay_date" => "2013-01-01 08:08:08"
     *          ]
     * else return [
     *          'code' => int // -1,-2,-3,-100 self define code ; 20000,20001,40001,40002,40004,40006 alipay error code
     *          'msg' => xxx
     *          'sub_code' => xxx
     *          'sub_msg' => xxx
     * ]
     * alipay interface @https://doc.open.alipay.com/docs/api.htm?spm=a219a.7395905.0.0.pe5xhq&docType=4&apiId=1321
     * alipay error code @https://doc.open.alipay.com/docs/doc.htm?treeId=291&articleId=105806&docType=1
     */
    public function fundTransToAccount($outBizNo,$payeeAccount,$amount,$payerShowName = '',$payeeRealName = '',$remark = '')
    {
        // init params
        $bizContentParams = [];
        $bizContentParams['out_biz_no'] = $outBizNo;
        $bizContentParams['payee_type'] = 'ALIPAY_LOGONID'; // only support alipay_loginid
        $bizContentParams['payee_account'] = $payeeAccount;
        $bizContentParams['amount'] = $amount;
        if (!empty($payerShowName)) {
            $bizContentParams['payer_show_name'] = $payerShowName;
        }
        if (!empty($payeeRealName)) {
            $bizContentParams['payee_real_name'] = $payeeRealName;
        }
        if (!empty($remark)) {
            $bizContentParams['remark'] = $remark;
        }
        $bizContentParams = json_encode($bizContentParams);

        try {
            $request = new \AlipayFundTransToaccountTransferRequest ();
            $request->setBizContent($bizContentParams);
            $result = $this->aop->execute($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            if (empty($result)) {
                return ['code'=> -1,'msg'=> 'gate way接口返回为空'];
            }
            if (!isset($result->$responseNode) || empty($result->$responseNode)) {
                return ['code'=> -2,'msg'=> $responseNode. '接口返回为空'];
            }
            if (!isset($result->$responseNode->code) || empty($result->$responseNode->code)) {
                return ['code'=> -3,'msg'=> '支付宝接口没有返回code值'];
            }
            $resultCode = $result->$responseNode->code;
            if ($resultCode == 10000) {
                return [
                    'code'=> $resultCode,
                    'msg'=> $result->$responseNode->msg,
                    'order_id'=> $result->$responseNode->order_id,
                    'out_biz_no'=> $result->$responseNode->out_biz_no,
                    'pay_date' => $result->$responseNode->pay_date,
                ];
            } else {
                return [
                    'code'=> $resultCode,
                    'msg'=> $result->$responseNode->msg,
                    'sub_code'=>$result->$responseNode->sub_code,
                    'sub_msg'=>$result->$responseNode->sub_msg,
                ];
            }

        }catch (\Exception $exception) {
            return ['code'=> -100,'msg'=> $exception->getMessage(),'file'=> $exception->getFile(),'line'=> $exception->getLine()];
        }
    }

    /*
     * params @out_biz_no OPTIONAL 	商户转账唯一订单号：发起转账来源方定义的转账单据ID。 和支付宝转账单据号不能同时为空。当和支付宝转账单据号同时提供时，将用支付宝转账单据号进行查询，忽略本参数。
     * params @order_id OPTIONAL  支付宝转账单据号：和商户转账唯一订单号不能同时为空。当和商户转账唯一订单号同时提供时，将用本参数进行查询，忽略商户转账唯一订单号。
     *
     * if success return [
     *          'code' => 10000,
     *          'msg' => "Success"
     *          "order_id" => "20160627110070001502260006780837",
     *          "out_biz_no" => "3142321423432",
     *          "pay_date" => "2013-01-01 08:08:08",
     *          "status" =>''(OPTIONAL)
     *
     * ] else return [
     *          'code' => int // -1,-2,-3,-99,-100 self define code ; 20000,20001,40001,40002,40004,40006 alipay error code
     *          'msg' => xxx
     *          'sub_code' => xxx
     *          'sub_msg' => xxx
     * ]
     * alipay interface @https://doc.open.alipay.com/docs/api.htm?spm=a219a.7395905.0.0.yAESCD&docType=4&apiId=1322
     * alipay error code @https://doc.open.alipay.com/docs/doc.htm?treeId=291&articleId=105806&docType=1
    */
    public function fundTransOrderQuery($outBizNo = '',$orderId = '')
    {
        if (empty($outBizNo) && empty($orderId)) {
            return ['code' => -99, 'msg' => 'out_biz_no and order_id are both empty'];
        }
        $bizContentParams = [];
        if (!empty($outBizNo)) {
            $bizContentParams['out_biz_no'] = $outBizNo;
        }
        if (!empty($orderId)) {
            $bizContentParams['order_id'] = $orderId;
        }
        $bizContentParams = json_encode($bizContentParams);
        
        try {
            $request = new \AlipayFundTransOrderQueryRequest ();
            $request->setBizContent($bizContentParams);
            $result = $this->aop->execute ( $request);

            if (empty($result)) {
                return ['code'=> -1,'msg'=> 'gate way接口返回为空'];
            }
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            if (!isset($result->$responseNode) || empty($result->$responseNode)) {
                return ['code'=> -2,'msg'=> $responseNode. '接口返回为空'];
            }
            if (!isset($result->$responseNode->code) || empty($result->$responseNode->code)) {
                return ['code'=> -3,'msg'=> '支付宝接口没有返回code值'];
            }
            $resultCode = $result->$responseNode->code;
            if ($resultCode == 10000) {
                return [
                    'code'=> $resultCode,
                    'msg'=> $result->$responseNode->msg,
                    'order_id'=> $result->$responseNode->order_id,
                    'status'=> isset($result->$responseNode->status)?$result->$responseNode->status:'',
                    'pay_date' => $result->$responseNode->pay_date,
                    'out_biz_no' => $result->$responseNode->out_biz_no,
                ];
            } else {
                return [
                    'code'=> $resultCode,
                    'msg'=> $result->$responseNode->msg,
                    'sub_code'=>$result->$responseNode->sub_code,
                    'sub_msg'=>$result->$responseNode->sub_msg,
                    'fail_reason'=>isset($result->$responseNode->fail_reason)?$result->$responseNode->fail_reason:'',
                    'error_code'=>isset($result->$responseNode->error_code)?$result->$responseNode->error_code:'',
                ];
            }
        } catch (\Exception $exception) {
            return ['code'=> -100,'msg'=> $exception->getMessage(),'file'=> $exception->getFile(),'line'=> $exception->getLine()];
        }
    }

}