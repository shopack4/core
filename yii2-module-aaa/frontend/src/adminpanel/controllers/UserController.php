<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use shopack\aaa\frontend\adminpanel\models\UserChangeImageForm;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\UserModel;
use shopack\aaa\frontend\common\models\UserSearchModel;
use shopack\aaa\frontend\adminpanel\models\PasswordResetForm;
use shopack\aaa\frontend\adminpanel\models\SetDeadtimeForm;
use shopack\aaa\frontend\adminpanel\models\UserSendMessageForm;
use yii\web\BadRequestHttpException;
use yii\web\UnprocessableEntityHttpException;

class UserController extends BaseCrudController
{
    public $modelClass = UserModel::class;
    public $searchModelClass = UserSearchModel::class;

    // public function actionProfile()
    // {
    // 	if (Yii::$app->user->isGuest)
    // 		return $this->goHome();

  //   return $this->render('profile/profile', [
  //     'model' => $this->findModel(Yii::$app->user->id),
  //   ]);
    // }

    /**
     * used for dropdowns (allow search and lazy loading)
     * #fixedPaging using ActiveDataProvider
     */
    public function actionSelect2List($q = null, $id = null, $page = 0, $perPage = 20)
    {
        $out['total_count'] = 0;
        $out['items'] = [
            [
                'id'        => '',
                'name'    => '',
                // 'firstname' => '',
                // 'lastname' => '',
                // 'email' => '',
            ],
        ];

        if (!empty($q)) {
            $q = strtolower(trim($q));
            $query = UserModel::find()
                ->addUrlParameter('q', $q);

            $dataProvider = new RestClientDataProvider([
                'query' => $query,
                'sort' => [
                    'attributes' => [
                        'usrFirstName',
                        'usrLastName',
                        'usrEmail',
                    ],
                    'defaultOrder' => [
                        'usrFirstName' => SORT_ASC,
                        'usrLastName' => SORT_ASC,
                        'usrEmail' => SORT_ASC,
                    ],
                ],
                'pagination' => [
                    'pageSize' => $perPage,
                ],
            ]);

            $cnt = $dataProvider->getTotalCount();
            $models = array_values($dataProvider->getModels());
            $arr = [];

            if (!empty($models)) {
                foreach ($models as $model) {
                    $arr[] = [
                        'id'   => $model->usrID,
                        'name' => $model->displayName(),
                    ];
                }
            }

            $out['total_count'] = $cnt;
            $out['items'] = $arr;
        } elseif ($id > 0) {
            $model = UserModel::findOne($id);
            $out['total_count'] = 1;
            $out['items'] = [
                [
                    'id'   => $id,
                    'name' => $model->displayName(),
                ],
            ];
        }

        return $this->renderJson($out);
    }

    public function actionPasswordReset($id)
    {
        $model = $this->findModel($id);

        if ($model->isSoftDeleted())
            throw new UnprocessableEntityHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

        $model = new PasswordResetForm();
        $model->userID = $id;

        $formPosted = $model->load(Yii::$app->request->getBodyParams());
        $done = false;
        if ($formPosted)
            $done = $model->process();

        if ($done) {
            return $this->renderJson([
                'message' => Yii::t('app', 'Success'),
                'modalDoneFragment' => $this->modalDoneFragment,
            ]);
        }

        if ($formPosted) {
            return $this->renderJson([
                'status' => 'Error',
                'message' => Yii::t('app', 'Error'),
                'error' => Html::errorSummary($model),
            ]);
        }

        return $this->renderAjaxModal('_form_password_reset', [
            'model' => $model,
        ]);
    }

    public function actionSendMessage($id = null)
    {
        $model = new UserSendMessageForm;
        $model->userID = $id;

        $formPosted = $model->load(Yii::$app->request->getBodyParams());
        $done = false;
        if ($formPosted)
            $done = $model->process();

        if (Yii::$app->request->isAjax) {
            if ($done) {
                return $this->renderJson([
                    'message' => Yii::t('app', 'Success'),
                    // 'id' => $id,
                    // 'redirect' => $this->doneLink ? call_user_func($this->doneLink, $model) : null,
                    // 'modalDoneFragment' => $this->modalDoneFragment,
                ]);
            }

            if ($formPosted) {
                return $this->renderJson([
                    'status' => 'Error',
                    'message' => Yii::t('app', 'Error'),
                    // 'id' => $id,
                    'error' => Html::errorSummary($model),
                ]);
            }

            return $this->renderAjaxModal('_form_sendMessage', [
                'model' => $model,
            ]);
        }

        if ($done) {
            if (empty($id))
                return $this->redirect(['index']);

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('sendMessage', [
            'model' => $model,
        ]);
    }

    public function actionUpdateImage($id)
    {
        $model = new UserChangeImageForm();
        $model->userID = $id;

        $formPosted = $model->load(Yii::$app->request->getBodyParams());
        $done = false;
        if ($formPosted)
            $done = $model->process();

        if (Yii::$app->request->isAjax) {
            if ($done) {
                return $this->renderJson([
                    'message' => Yii::t('app', 'Success'),
                    // 'id' => $id,
                    // 'redirect' => $this->doneLink ? call_user_func($this->doneLink, $model) : null,
                    // 'modalDoneFragment' => 'details',
                ]);
            }

            if ($formPosted) {
                return $this->renderJson([
                    'status' => 'Error',
                    'message' => Yii::t('app', 'Error'),
                    // 'id' => $id,
                    'error' => Html::errorSummary($model),
                ]);
            }

            return $this->renderAjaxModal('_form_image', [
                'model' => $model,
            ]);
        }

        if ($done)
            return $this->redirect(['index']);

        return $this->render('updateImage', [
            'model' => $model,
        ]);
    }

    public function actionSetDeadtime($id)
    {
        $model = $this->findModel($id);

        if ($model->isSoftDeleted())
            throw new UnprocessableEntityHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

        $model = new SetDeadtimeForm();
        $model->userID = $id;

        $formPosted = $model->load(Yii::$app->request->getBodyParams());
        $done = false;
        if ($formPosted)
            $done = $model->process();

        if ($done) {
            return $this->renderJson([
                'message' => Yii::t('app', 'Success'),
                'modalDoneFragment' => $this->modalDoneFragment,
            ]);
        }

        if ($formPosted) {
            return $this->renderJson([
                'status' => 'Error',
                'message' => Yii::t('app', 'Error'),
                'error' => Html::errorSummary($model),
            ]);
        }

        return $this->renderAjaxModal('_form_set_deadtime', [
            'model' => $model,
        ]);
    }

    public function actionRemoveDeadtime($id)
    {
        $bodyParams = Yii::$app->request->getBodyParams();
        if (empty($bodyParams['confirmed']))
            throw new UnprocessableEntityHttpException('این عملیات باید تایید شده باشد');

        if (Yii::$app->request->isAjax == false)
            throw new BadRequestHttpException('It is not possible to execute this command in a mode other than Ajax');

        $done = UserModel::doRemoveDeadtime($id);

        return $this->renderJson([
            'status' => 'Ok',
            'message' => Yii::t('app', 'Success'),
            // 'modalDoneFragment' => $this->modalDoneFragment,
        ]);
    }
}
