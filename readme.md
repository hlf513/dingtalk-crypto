# DingTalkCrypto

钉钉开放平台的消息体加解密（`openssl`加密版本）；因为`php7`以后`mcrypt`扩展被舍弃

## Install

```
composer require hlf_513/dingtalk-crypto
```

## Usage

```
$crypto = new Crypto(
    $token,
    $encodingAesKey,
    $suiteKey
);

$string = 'success';
$timestamp = '1515664989185';
$nonce = '53IP1CdM';

// 加密
$ret = $crypto->encryptMsg($string, $timestamp, $nonce);
$ret = json_decode($ret, 1);
// 解密
$ret = $crypto->decryptMsg($ret['msg_signature'], $timestamp, $nonce, $ret['encrypt']);
// output: $ret === 'success'
```