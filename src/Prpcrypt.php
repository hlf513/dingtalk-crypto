<?php
/**
 * Prpcrypt
 *
 * User: longfei.he <hlf513@gmail.com>
 * Date: 2018/1/12
 */

namespace Hlf\DingTalkCrypto;



class Prpcrypt
{
	protected $key;

	protected $PKCS7Encoder;


	public function __construct($k)
	{
		$this->key = base64_decode($k . "=");
		$this->PKCS7Encoder = new PKCS7Encoder();
	}

	/**
	 *
	 *
	 * @param $text
	 * @param $corpid
	 *
	 * @return string
	 * @throws Exception
	 */
	public function encrypt($text, $corpid)
	{
		try {
			//获得16位随机字符串，填充到明文之前
			$random = $this->getRandomStr();
			$text = $random . pack("N", strlen($text)) . $text . $corpid;
			// 网络字节序
			$iv = substr($this->key, 0, 16);
			//使用自定义的填充方式对明文进行补位填充
			$text = $this->PKCS7Encoder->encode($text);

			return openssl_encrypt($text, 'AES-256-CBC', substr($this->key, 0, 32), OPENSSL_ZERO_PADDING, $iv);

		} catch (\Exception $e) {
			throw new Exception('Encrypt AES error');
		}
	}

	/**
	 *
	 *
	 * @param $encrypted
	 * @param $corpid
	 *
	 * @return array|string
	 * @throws Exception
	 */
	public function decrypt($encrypted, $corpid)
	{
		try {
			$iv = substr($this->key, 0, 16);
			$decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', substr($this->key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
		} catch (\Exception $e) {
			throw new Exception('decrypt AES error');
		}

		try {
			//去除补位字符
			$result = $this->PKCS7Encoder->decode($decrypted);
			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16)
				return "";
			$content = substr($result, 16, strlen($result));
			$len_list = unpack("N", substr($content, 0, 4));
			$xml_len = $len_list[1];
			$xml_content = substr($content, 4, $xml_len);
			$from_corpid = substr($content, $xml_len + 4);
		} catch (\Exception $e) {
			throw new Exception('decrypt AES error');
		}
		if ($from_corpid != $corpid) {
			throw new Exception('Validate SuiteKey error');
		}

		return $xml_content;
	}

	function getRandomStr()
	{
		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($str_pol) - 1;
		for ($i = 0; $i < 16; $i++) {
			$str .= $str_pol[mt_rand(0, $max)];
		}

		return $str;
	}
}