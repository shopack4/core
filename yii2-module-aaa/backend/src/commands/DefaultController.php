<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\commands;

use Yii;
use yii\console\ExitCode;
use yii\console\Controller;

/*

-- MIGRATE:
cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii migrate/up --interactive 0 2>&1 >>logs/migrate.log

-- MESSAGE:
cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/message/process-queue 2>&1 >>logs/aaa_message_process-queue.log

-- FILE:
cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/file/process-queue 200 2>&1 >>logs/aaa_file_process-queue.log

-- BIRTHDAY:
0 15 30 45
cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/message/send-birthday-greetings 2>&1 >>logs/aaa_message_send-birthday-greetings.log



cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/default/new-keys 2>&1 >>logs/new-keys.log

cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/default/heartbeat 2>&1 >>logs/aaa-heartbeat.log

*/

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

		$this->log("************ Private key: ************");
		$this->log($privkey . "\n");

		$this->log("************ Public key: ************");
		$this->log($pubkey . "\n");

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
