<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\commands;

use Yii;
use yii\console\ExitCode;
use yii\console\Controller;

class DefaultController extends Controller
{
  public function log($message, $type='INFO')
  {
		if (Yii::$app->isConsole == false)
			return;

    if ($message instanceof \Throwable) {
			$message = $message->getMessage();
      $type = 'ERROR';
    }

		if (empty($type))
    	echo "[" . date('Y/m/d H:i:s') . "] {$message}\n";
		else
    	echo "[" . date('Y/m/d H:i:s') . "][{$type}] {$message}\n";
  }

	public function actionNewKeys($alg='sha256', $size=2048)
	{
		$config = [
			"digest_alg" => $alg,
			"default_md" => $alg,
			"private_key_bits" => $size,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		];

		$res = openssl_pkey_new($config);

		// Get private key
		$privkey = '';
		openssl_pkey_export($res, $privkey, null, $config);

		// Get public key
		$info = openssl_pkey_get_details($res);
		$pubkey = $info['key'];

		$this->log("************ Private key: ************");
		$this->log($privkey . "\n");

		$this->log("************ Public key: ************");
		$this->log($pubkey . "\n");

		$type = $info['type'];

		if ($type == OPENSSL_KEYTYPE_RSA)
			$keytype = 'rsa';
		else if ($type == OPENSSL_KEYTYPE_DSA)
			$keytype = 'dsa';
		else if ($type == OPENSSL_KEYTYPE_DH)
			$keytype = 'dh';
		else if ($type == OPENSSL_KEYTYPE_EC)
			$keytype = 'ec';

		if (isset($keytype)) {
			$typeInfo = $info[$keytype];

			$this->log("************ key info ({$keytype}): ************");
			$out = "{\n";
			foreach ($typeInfo as $k => $v) {
				$out .= "\t\"{$k}\": \"" . base64_encode($v) . "\"\n";
			}
			$out .= "}\n";
			$this->log($out . "\n");
		}

		/*
       rsa -> jedt
			-------------
			   n : n
			   e : e
			   d : d
			   p : p
			   q : q
			dmp1 : dp
			dmq1 : dq
			iqmp : di
			     : kty => "RSA"
			     : kid => ???
		*/

    return ExitCode::OK;
	}

	public function actionHeartbeat()
	{
		try {
			$this->removeOldActionLogs();
		} catch (\Throwable $e) {
      $this->log($e);
      Yii::error($e, __METHOD__);
		}

		// try {
		// 	$this->removeExpiredBasketItems();
		// } catch (\Throwable $e) {
    //   $this->log($e);
    //   Yii::error($e, __METHOD__);
		// }

		return ExitCode::OK;
	}

	protected function removeOldActionLogs()
	{
		$qry =<<<SQL
DELETE FROM tbl_SYS_ActionLogs
	WHERE atlAt <= DATE_SUB(NOW(), INTERVAL 3 MONTH)
;
SQL;

		Yii::$app->db->createCommand($qry)->execute();
	}

}
