<?php
namespace AliPay;

class AliPayConfig
{
    // online and test common config
    const API_VERSION = '1.0';
    const SIGN_TYPE = 'RSA2';
    const POST_CHARSET = 'utf-8';
    const FORMAT = 'json';

    //online
    const GATE_WAY_URL = 'https://openapi.alipay.com/gateway.do';
    const APP_ID = '';
    // 请填写开发者私钥去头去尾去回车，一行字符串
    const RSA_PRIVATE_KEY = '';
    // 请填写支付宝公钥，一行字符串
    const ALI_PAY_RSA_PUBLIC_KEY = '';

    // 沙箱环境 test
    const GATE_WAY_URL_TEST = 'https://openapi.alipaydev.com/gateway.do';
    const APP_ID_TEST = '';
    const RSA_PRIVATE_KEY_TEST = '';
    const ALI_PAY_RSA_PUBLIC_KEY_TEST = '';

}