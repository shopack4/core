<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\commands;

use Yii;
use yii\db\Expression;
use yii\console\Controller;
use yii\console\ExitCode;

/*
cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/file/process-queue 2>&1 >>logs/aaa_file_process-queue.log
*/

class FileController extends Controller
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

  //must be called by cron
  public function actionProcessQueue($maxItemCount = 100)
  {
    try {
      Yii::$app->fileManager->processQueue($maxItemCount);
		} catch(\Exception $e) {
      $this->log($e);
			Yii::error($e, __METHOD__);
		}

    return ExitCode::OK;
  }

}
