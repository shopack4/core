<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use shopack\base\common\helpers\HttpHelper;

class ImageChangeForm extends Model
{
	use \shopack\base\common\models\UploadedFilesTrait;

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
    Yii::$app->user->assertIsNotGuest();

    // if ($this->validate() == false)
    //   throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

		$files = $this->getUploadedFilesData();
		if (empty($files))
			throw new NotFoundHttpException('nothing to do');

    //--
    $apiResponse = HttpHelper::callApi('aaa/user/update-image',
      HttpHelper::METHOD_POST,
      [
        'id' => Yii::$app->user->id,
      ],
      [],
      $files,
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return true;
  }

}
