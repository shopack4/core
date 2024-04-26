<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\commands;

use Yii;
use yii\console\ExitCode;
use yii\console\Controller;

/*

cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/default/new-keys 2>&1 >>logs/new-keys.log

cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/default/heartbeat 2>&1 >>logs/heartbeat.log

*/

class DefaultController extends Controller
{
	public function actionNewKeys()
	{
		$config = [
			"digest_alg" => "sha256",
			"default_md" => "sha256",
			"private_key_bits" => 2048,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		];

		$res = openssl_pkey_new($config);

		// Get private key
		$privkey = '';
		openssl_pkey_export($res, $privkey, null, $config);

		// Get public key
		$pubkey = openssl_pkey_get_details($res);
		$pubkey = $pubkey['key'];

		echo "************ Private key: ************\n";
		echo $privkey . "\n\n";

		echo "************ Public key: ************\n";
		echo $pubkey . "\n\n";

    return ExitCode::OK;
	}

	public function actionHeartbeat()
	{
		try {
			$this->removeOldActionLogs();
		} catch (\Throwable $e) {
      echo $e->getMessage();
      Yii::error($e, __METHOD__);
		}

		// try {
		// 	$this->removeExpiredBasketItems();
		// } catch (\Throwable $e) {
    //   echo $e->getMessage();
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
