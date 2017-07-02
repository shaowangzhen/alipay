# alipay
**前言**
  
      github上有很多支持 支付宝支付、转账等等的代码库，看了下，他们的做法一般都是，改写支付宝的sdk,并开放出来接口给大家来使用；
      但是牵涉到钱，不是官网提供的代码，总是不让人那么放心！并且支付宝如果修复了一个bug,那么这些代码库也不方便更新！

      因此，还是直接用支付宝官方提供的sdk 吧；
所以，我的这个alipay 库，目标是，提供在lumen 或者 laravel 引入支付宝sdk，以及后续
升级sdk 的方法；并且，同时，在alipay.php 中提供了 基于支付宝官方sdk 封装的转账和查询转账结果的两个接口；希望小伙伴们，也可以按照
现在的方式，把你们基于支付宝sdk 封装的，并且使用安全的接口，维护到 alipay.php 中，期待你的code!

**文件结构**

1.AliPay

  AliPay/AliPay.php 对支付宝 转账、以及转账结果查询的接口：
    
    转账接口：fundTransToAccount($outBizNo,$payeeAccount,$amount,$payerShowName = '',$payeeRealName = '',$remark = '')

    转账查询接口：fundTransOrderQuery($outBizNo = '',$orderId = '')
  AliPay/AliPayConfig.php 支付宝账号的配置信息；
  
  
2.vendor

  在vendor中新建alipay-sdk-php 文件夹，然后从官网https://doc.open.alipay.com/doc2/detail?treeId=54&articleId=103419&docType=1
  下载 php版本的 sdk
  
  把sdk中的aop文件夹copy 到 alipay-sdk-php 文件中（忽略 sdk中lotusphp_runtime 和 AopSdk.php）

3.composer.json

  在composer.json 的 autoload 节点里加入： 
  ``` 
  "classmap": [
      "vendor/alipay-sdk-php/aop"
    ]
  ``` 
  运行``composer dump-autoload``
  在项目里不用 require ，直接： 
  ``` 
  $a = new \AopClient(); 
  $b = new \AlipayAppTokenGetRequest(); 
  $c = $a->execute($b); 
  ```
  
  
**有问题随时联系 谢谢**