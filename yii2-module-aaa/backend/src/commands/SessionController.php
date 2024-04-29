<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use shopack\aaa\backend\models\SessionModel;

class SessionController extends Controller
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
  public function actionRemoveExpired()
  {
    //do not use `=` in condition for sleegy
    $count = SessionModel::deleteAll('ssnExpireAt < NOW()');

    $this->log("deleted count: {$count}");

    return ExitCode::OK;
  }
}
