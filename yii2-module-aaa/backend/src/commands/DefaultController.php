<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\commands;

use yii\console\ExitCode;
use yii\console\Controller;

/*
cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/default/new-keys 2>&1 >>logs/new-keys.log
*/

class DefaultController extends Controller
{
	public function actionNewKeys()
	{
		$res = openssl_pkey_new();

		// Get private key
		openssl_pkey_export($res, $privkey);

		// Get public key
		$pubkey = openssl_pkey_get_details($res);
		$pubkey = $pubkey['key'];

		echo "************ Private key: ************\n";
		echo $privkey . "\n\n";

		echo "************ Public key: ************\n";
		echo $pubkey . "\n\n";

    return ExitCode::OK;
	}

}
