<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use shopack\aaa\backend\models\ApprovalRequestModel;
use shopack\base\backend\helpers\AuthHelper;
use shopack\base\common\helpers\GeneralHelper;

//<a href='{{panel-address}}/aaa/auth/accept-approval?input={{email}}&code={{code}}'>Accept</a>
class ApproveCodeForm extends Model
{
  public $input;
  public $code;

  public function rules()
  {
    return [
      ['input', 'required'],
      ['code', 'required'],
    ];
  }

  public function approve()
  {
    if ($this->validate() == false)
      return false;

    // list ($normalizedInput, $inputType) = GeneralHelper::checkLoginPhrase($this->input, false);

		$result = ApprovalRequestModel::acceptCode($this->input, $this->code);

    if ($result === false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    if (isset($result['emailChanged'])) {
      AuthHelper::logout();
      return [
        'token' => null,
      ];
    }

		return [
			'result' => true,
		];
  }

}
