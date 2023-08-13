<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/*
cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/message/process-queue 2>&1 >>logs/aaa_message_process-queue.log

cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/message/send-birthday-greetings 2>&1 >>logs/aaa_message_send-birthday-greetings.log
*/

class MessageController extends Controller
{
  public function actionTestSendEmail()
  {
    /*
    cd /home2/iranhmus/domains/api.iranhmusic.ir/public_html; /usr/local/php-8.1/bin/php yii aaa/message/test-send-email 2>&1 >>logs/aaa_test-send-email.log
    */

    if (!YII_DEBUG) {
      echo "NOT IN DEBUG MODE";
      return;
    }

    $email = Yii::$app->mailer
      ->compose(
        //['html' => 'aaa-html', 'text' => 'aaa-text'],
        //['user' => $user]
      )
      ->setFrom(Yii::$app->params['senderEmail'])
      ->setTo("kambizzandi@gmail.com")
      ->setSubject('test email 1234 subject')
      ->setTextBody('test email 1234 body')
      ->setHtmlBody('test email 1234 body');

    $result = $email->send();

    var_dump($result);
  }

  //must be called by cron
  public function actionProcessQueue($maxItemCount = 100)
  {
    try {

      Yii::$app->messageManager->processQueue($maxItemCount);

		} catch(\Exception $e) {
      echo $e->getMessage();
			Yii::error($e, __METHOD__);
		}

    return ExitCode::OK;
  }

    //must be called by cron
    public function actionSendBirthdayGreetings()
    {
      try {

        $rowsCount = Yii::$app->messageManager->sendBirthdayGreetings();
        if ($rowsCount > 0)
          echo "new messages: {$rowsCount}";

      } catch(\Exception $e) {
        echo $e->getMessage();
        Yii::error($e, __METHOD__);
      }

      return ExitCode::OK;
    }

}
