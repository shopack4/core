<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\helpers;

use Yii;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\helpers\Html;
use shopack\aaa\frontend\common\models\ChallengeForm;

class AuthHelper
{
  static function redirectToChallenge(ChallengeForm $challengeModel)
  {
    $request = Yii::$app->getRequest();
    $csrfMetaTags = Html::csrfMetaTags();
    $csrfToken = $request->getCsrfToken();
    $challengeUrl = Url::to(['challenge']);
    $formName = $challengeModel->formName();

    $html =<<<HTML
<html>
<head>
{$csrfMetaTags}
</head>
<body onload="document.redirectform.submit()">
<form method="POST" action="{$challengeUrl}" name="redirectform">
<input type="hidden" name="{$request->csrfParam}" value="{$csrfToken}">
<input type="hidden" name="{$formName}[realm]" value="{$challengeModel->realm}">
<input type="hidden" name="{$formName}[type]" value="{$challengeModel->type}">
<input type="hidden" name="{$formName}[key]" value="{$challengeModel->key}">
<input type="hidden" name="{$formName}[rememberMe]" value="{$challengeModel->rememberMe}">
</form>
</body>
</html>
HTML;
    Yii::$app->controller->layout = false;
    return Yii::$app->controller->renderContent($html);
  }

}
