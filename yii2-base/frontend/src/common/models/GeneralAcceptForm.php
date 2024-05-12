<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\models;

use yii\base\Model;

class GeneralAcceptForm extends Model
{
	public $message;

	//fields
	public $posted = 1;

	public function rules()
	{
		return [
			['posted', 'required'],
		];
	}

}
