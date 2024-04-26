<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\security;

use OpenSSLAsymmetricKey;
use Yii;
use yii\base\BaseObject;

//https://github.com/xjflyttp/yii2-rsa
class RsaPublic extends BaseObject
{
	/**
	 * Certificate
	 * @var string
	 * @see http://cn2.php.net/manual/en/function.openssl-pkey-get-public.php
	 */
	public $key;

	/**
	 * Key Pass
	 * @var string
	 * @see http://cn2.php.net/manual/en/function.openssl-pkey-get-public.php
	 */
	public $passphrase = '';

	/**
	 * Factory
	 * @param string $key KeyPath | KeyContent
	 * @param string $passphrase
	 * @return RsaPublic
	 * @throws \yii\base\Exception
	 */
	public static function model($key, $passphrase = '') {
		if (substr($key, 0, 1) === '@') {
			$key = 'file://' . Yii::getAlias($key);
		}
		return new static([
			'key' => $key,
			'passphrase' => $passphrase,
		]);
	}

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * getPrivateKey
	 * @return OpenSSLAsymmetricKey|FALSE
	 * @see http://cn2.php.net/manual/en/function.openssl-get-publickey.php
	 */
	private function getKey() {
		return openssl_pkey_get_public($this->key);
	}

	/**
	 * getBits
	 * @return int
	 */
	private function getCertBits() {
		$detail = openssl_pkey_get_details($this->getKey());
		return (isset($detail['bits'])) ? $detail['bits'] : null;
	}

	private function getCertChars() {
		$certLength = $this->getCertBits();
		return $certLength / 8;
	}

	private function getMaxEncryptCharSize() {
		return $this->getCertChars() - 11;
	}

	/**
	 * encrypt
	 * @param string $data
	 * @return string|null
	 * @see http://cn2.php.net/manual/en/function.openssl-public-encrypt.php
	 */
	public function encrypt($data) {
		$output = '';
		$key = $this->getKey();
		$chunkSize = $this->getMaxEncryptCharSize();
		$chunks = str_split($data, $chunkSize);

		foreach ($chunks as $chunk) {
		// while ($data) {
		// 	$chunk = substr($data, 0, $chunkSize);
		// 	$data = substr($data, $chunkSize);
			$encrypted = '';
			$result = openssl_public_encrypt($chunk, $encrypted, $key);

			if ($result === false) {
				$error = openssl_error_string();
				return null;
			}

			$output .= $encrypted;
		}

		return base64_encode($output);
	}

	/**
	 * decrypt
	 * @param string $data
	 * @return string|null
	 * @see http://cn2.php.net/manual/en/function.openssl-public-decrypt.php
	 */
	public function decrypt($data) {
		$output = '';
		$key = $this->getKey();
		$data = base64_decode($data);
		$chunkSize = $this->getCertChars();
		$chunks = str_split($data, $chunkSize);

		foreach ($chunks as $chunk) {
		// while ($data) {
		// 	$chunk = substr($data, 0, $chunkSize);
		// 	$data = substr($data, $chunkSize);
			$decrypted = '';
			$result = openssl_public_decrypt($chunk, $decrypted, $key);

			if ($result === false) {
				$error = openssl_error_string();
				return null;
			}

			$output .= $decrypted;
		}

		return $output;
	}

}
