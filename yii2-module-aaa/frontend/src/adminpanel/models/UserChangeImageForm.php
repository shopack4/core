<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use yii\web\NotFoundHttpException;
use shopack\base\common\helpers\HttpHelper;

class UserChangeImageForm extends Model
{
	use \shopack\base\common\models\UploadedFilesTrait;

	public $userID;
  public $postback;
  public $image;

  public function rules()
  {
    return [
      ['postback', 'required'],
      ['image', 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'postback' => Yii::t('aaa', 'postback'),
			'image' => Yii::t('aaa', 'Official Personal Photo'),
		];
	}

  public function process()
  {
    // if ($this->validate() == false)
    //   throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

		$files = $this->getUploadedFilesData();
		if (empty($files))
			throw new NotFoundHttpException('nothing to do');

    //--
    $apiResponse = HttpHelper::callApi('aaa/user/update-image',
      /* $method */     HttpHelper::METHOD_POST,
      /* $urlParams */  [
                          'id' => $this->userID,
                        ],
      /* $bodyParams */ [],
      /* $formFiles */  $files,
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return true;
  }

}
