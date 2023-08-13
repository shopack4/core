<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
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
			'image' => Yii::t('aaa', 'Image'),
		];
	}

  public function process()
  {
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    // if ($this->validate() == false)
    //   throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

		$files = $this->getUploadedFilesData();
		if (empty($files))
			throw new NotFoundHttpException('nothing to do');

    //--
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/user/update-image',
      HttpHelper::METHOD_POST,
      [
        'id' => Yii::$app->user->id,
      ],
      [],
      $files,
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
  }

}
