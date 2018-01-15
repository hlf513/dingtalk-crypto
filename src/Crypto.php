<?php
/**
 * DingTalkCrypto
 *
 * User: longfei.he <hlf513@gmail.com>
 * Date: 2018/1/12
 */

namespace Hlf\DingTalkCrypto;

class Crypto
{
	private $m_token;
	private $m_encodingAesKey;
	private $m_suiteKey;

	protected $prpcrypt;

	/**
	 * Crypto constructor.
	 *
	 * @param $token
	 * @param $encodingAesKey
	 * @param $suiteKey
	 *
	 * @throws Exception
	 */
	public function __construct($token, $encodingAesKey, $suiteKey)
	{
		$this->m_token = $token;
		$this->m_encodingAesKey = $encodingAesKey;
		$this->m_suiteKey = $suiteKey;

		if (strlen($this->m_encodingAesKey) != 43) {
			throw new Exception('Illegal Aes key');
		}

		$this->prpcrypt = new Prpcrypt($this->m_encodingAesKey);
	}


	/**
	 * 加密
	 *
	 * @param string $plain
	 * @param int    $timeStamp
	 * @param string $nonce
	 *
	 * @return string
	 * @throws Exception
	 */
	public function encryptMsg($plain, $timeStamp, $nonce)
	{
		$encrypt = $this->prpcrypt->encrypt($plain, $this->m_suiteKey);

		if ($timeStamp == null) {
			$timeStamp = time();
		}
		$signature = $this->getSignature($timeStamp, $nonce, $encrypt);

		return json_encode(array(
			"msg_signature" => $signature,
			"encrypt"       => $encrypt,
			"timeStamp"     => $timeStamp,
			"nonce"         => $nonce
		));
	}

	/**
	 * 解密
	 *
	 * @param string $signature
	 * @param int    $timeStamp
	 * @param string $nonce
	 * @param string $encrypt
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function decryptMsg($signature, $timeStamp, $nonce, $encrypt)
	{
		if ($timeStamp == null) {
			$timeStamp = time();
		}
		$verifySignature = $this->getSignature($timeStamp, $nonce, $encrypt);
		if ($verifySignature != $signature) {
			throw new Exception('Validate signature');
		}

		return $this->prpcrypt->decrypt($encrypt, $this->m_suiteKey);
	}

	protected function getSignature($timestamp, $nonce, $encrypt_msg)
	{
		$array = array($encrypt_msg, $this->m_token, $timestamp, $nonce);
		sort($array, SORT_STRING);
		$str = implode($array);

		return sha1($str);
	}
}