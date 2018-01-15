<?php

use Hlf\DingTalkCrypto\Crypto;


class DingTalkCryptotTest extends \PHPUnit_Framework_TestCase
{

	// todo 写入自己应用的相关数据
	protected $token = '';
	// todo 写入自己应用的相关数据
	protected $suiteKey = '';
	// todo 写入自己应用的相关数据
	protected $encodingAesKey = '';

	protected $crypto;

	public function __construct()
	{
		$this->crypto = new Crypto(
			$this->token,
			$this->encodingAesKey,
			$this->suiteKey
		);
	}

	/**
	 * @throws Exception
	 */
	public function testCrypt()
	{
		$string = 'success';
		$timestamp = '1515664989185';
		$nonce = '53IP1CdM';

		$ret = $this->crypto->encryptMsg($string, $timestamp, $nonce);
		$ret = json_decode($ret, 1);
		$this->assertNotNull($ret);
		$this->assertNotEmpty($ret['msg_signature']);
		$this->assertNotEmpty($ret['encrypt']);
		$ret = $this->crypto->decryptMsg($ret['msg_signature'], $timestamp, $nonce, $ret['encrypt']);
		$this->assertEquals($ret, 'success');
	}

}
