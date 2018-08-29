<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016091800543559",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEpAIBAAKCAQEAqtf7H7NhabWBBR83DFIbQt9F/F4YSgt+8EY7T2MIBK1qu++/6VLEHCh2Ew/Dee0Pk92SHouSejmvUBdVQy+aI9So+cUccp+CFfcDLKIY0m46mB98p++5KEQLWN2wU5pJU7QM+isGdCYq2/PWqBkd6YVrca5LIw5ribV2IS/gBvoonrbG7I6dnhg8bMD4dmQosuDjR1EHxuUPRiK14DndsV5fH3BmYUE3b/nMwwk2gBPWC/5CxJjO0vCNYmCwxkxGjHwAMCHLitbwpHTHPut1Ooz/Jv7f1STuuPE1wJuhRv6gvBnfb8w/Kl8oH8FzLZ6eirjxkFjGzg/qrPT7h+BIiQIDAQABAoIBAQCdYnavj3rP/SssYsM9kG4YvsMkaDKME+cOxkRhL+P1GyTWmVj8Qwjyv7t2d/EmY2MUXuv7Q6ze+EEu0S/0wueG1qQL5K/+UD9wqKu99F5VjMJh3a/irY6vL3Acn62OdvXSLG5AEF7TpU2abxlypiDOPeDrSxtg4injJM38aLRC97LgLzBoXJmuCDLDjx0Zoor33taxoQKk1mwShcDdZWRbGxBZe6+B7bzTFSrosVnu5nMEDwSHrEg/ZNud9ITIYjTYo1btldv/k1lUMG9qIBp0U1eBY5IIomL8QVCn+na+hz2+EPfTncZGtaGqCUCvYbuUztu6D/XMkmtOM69M7JsBAoGBAOKfADprYblGe7HK1Dts4TAG98urnss8DPip9tGq2F/Ven2K4QswN8XzAOkgrRluDCIl2IpyMF2/b6WgCEi9Y5U8EVG7qaft1n1gCgrLbSgJpsGY4Req/MA7gNXb1APF3+TLDVGx6Jay1SGEg6Ke4Y/5F+KrFiW8sNgHVrUbBhXZAoGBAMD93TNIkF4i1ok+JOBYvwTfiC74S7cEmij5iKDEVcXOHs5I8IEZ1DvP0Wd2t9xyuzWQMpdDGOdYobQXueUO/mW9tlZsejOeSyahlZtKRZcUXQtJzTPxLvEwv7BGSry71sJdP5TFA7tO+jq2Ox1lfNsMNmWjbX10wvb/e0uWVKoxAoGAEtsh/LpkljLsJd33jL5BemqKAbNU8hocBjC2LbnmaQrtNzbwBKtNaLYQdFVYsc37SngVrWdU6Of91S0co1jGyWsHEeLoeeWLPEFadI16lqM+8crTp9F9WE1bKfAxkuLK/1F52TtRXACjRTeucECCCiWyvBR6Mkeh+0eZClamSfkCgYEAqEFsBZcBDqFO6057tgCJYUVFJLYixMhFYu3S2V7y7MZ8gxqCW/vZ1d+kUJDnUVHRt6wk/01nO+NA75Mz5ekBkFAq1QQ0MiaSnHaJyV6id0owqHPKbLucUnlO1e8in5MgdEn58ckmLLp5XJCdz0444XyvPEOUZKlUhSVOKCNDYzECgYBeXQ8D+GctDpUACGQI7d2DAu+SvU5D2F6e4wsfoTCR6sMOMN+NdwVFH3Eu/fDufoFYyEdfD/DDTaZQLLbGS4QD9k59brye0eEeY/jAyXaxc4pfIWkOQpg4nfGgEW3rsCvGO78XAAfr7g6RerSwTPR16BcBFUl9oemuOobyvAw+Yw==",
		
		//异步通知地址
		'notify_url' => "114.116.80.12/alipay/notify_url.php",
		
		//同步跳转

		'return_url' => "114.116.80.12/alipay/return_url.php",


		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzYRb8AOksmCQ5c3Fx1btSc2gtyxYeaJ6JVXvrj0k6HtbGalFIMFu5YqC+QQWCNmYraVlmYDtGka5WwjNF7WuSzEhdTQzeSh0AeeLQwP6oJ7YlwU4YZz7mvso7gcGuJKO3YTt+SNyfIFDGhcJYFGB4suOo2geExaSs/9jAZxyzREyZa919cBsROzPF4a+Pmh/vn1elTKg2Cmjgjzf8UQ3FRdD9lbSLd9lugurDJqqPleUY4RHPMS6Ush7PQi569n+oeGtlZWfuHq31Frm408D7y5aIcnEJwVuMQTB3fxOl+XhBYTn1eDnFMsW5IWRqTWeOUTu2d08O5vJmf9ZXY8yuwIDAQAB",
		
	
);