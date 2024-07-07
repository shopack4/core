<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

 namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use yii\web\NotFoundHttpException;

class UpdateImageForm extends Model
{
  public $userID;

  public function rules()
  {
    return [
      ['userID', 'required'],
    ];
  }

  public function process()
  {
    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    $user = UserModel::findOne($this->userID);
    if ($user === null)
  		throw new NotFoundHttpException('The requested item does not exist.');

    $uploadResult = Yii::$app->fileManager->saveUploadedFiles(
      /* userID             */ $this->userID,
      /* targetPath         */ 'user',
      /* allowedFileTypes   */ null,
      /* allowedMimeTypes   */ ['image/png', 'image/gif', 'image/jpeg'],
      /* allowedMinFileSize */ 0,
      /* allowedMaxFileSize */ 2 * 1024 * 1024
    );

    if (empty($uploadResult))
      return false;

    $result = current($uploadResult);

    $user->usrImageFileID = $result['fileID'];
    $user->save();

    return true;
  }

}
