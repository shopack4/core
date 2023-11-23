<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\helpers;

use Yii;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;

class Html extends \yii\bootstrap5\Html
{
	public static function createButton($text = null, $url = null, $options = []) {
		if ($text == null)
			$text = Yii::t('app', 'Create');

		$url = array_replace_recursive(['create'], $url ?? []);
		$modal = ArrayHelper::remove($options, 'modal', true);

		$btn = ArrayHelper::remove($options, 'btn', 'success');

		return static::a($text, $url, array_replace_recursive([
			// 'title' => Yii::t('app', 'Create'),
			'class' => ['btn', 'btn-sm', 'btn-' . $btn],
			'modal' => $modal,
      // 'data-popup-size' => 'lg',
	 	], $options));
	}

	public static function updateButton($text = null, $url = null, $options = []) {
		if ($text == null)
			$text = Yii::t('app', 'Update');

		$url = array_replace_recursive(['update'], $url ?? []);
		$modal = ArrayHelper::remove($options, 'modal', true);

		$btn = ArrayHelper::remove($options, 'btn', 'primary');

		return static::a($text, $url, array_replace_recursive([
			// 'title' => Yii::t('app', 'Update'),
			'class' => ['btn', 'btn-sm', 'btn-' . $btn],
			'modal' => $modal,
      // 'data-popup-size' => 'lg',
	 	], $options));
	}

	public static function confirmButton($text, $url, $message, $options = []) {
		$disabled = ArrayHelper::remove($options, 'disabled', false);
		if ($disabled) {
			$options['data'] = array_merge($options['data'] ?? [], [
				'href' => Url::to($url),
			]);

			$url = 'javascript:void(0)';

			$options['class'] = array_merge($options['class'] ?? [], [
				'disabled'
			]);
		}

		$btn = ArrayHelper::remove($options, 'btn', 'danger');

		return static::a($text, $url, array_replace_recursive([
			'title' => $text,
			'class' => $btn ? ['btn', 'btn-sm', 'btn-' . $btn] : null,
			'data' => [
				'confirm' => $message,
				'method' => 'post',
				'params' => [
					'confirmed' => 1,
				],
				// 'data-pjax' => '0',
			],
	 	], $options));
	}

	public static function deleteButton($text = null, $url = null, $options = []) {
		if ($text == null)
			$text = Yii::t('app', 'Delete');

		$url = array_replace_recursive(['delete'], $url ?? []);

		return self::confirmButton($text, $url, Yii::t('app', 'Are you sure you want to delete this item?'), $options);
	}

	public static function undeleteButton($text = null, $url = null, $options = []) {
		if ($text == null)
			$text = Yii::t('app', 'Undelete');

		$url = array_replace_recursive(['undelete'], $url ?? []);

		return self::confirmButton($text, $url, Yii::t('app', 'Are you sure you want to un-delete this item?'), $options);
	}

	public static function a($text, $url = null, $options = []) {
		$isModal = ArrayHelper::remove($options, 'modal', false);
		$isAjax = ArrayHelper::remove($options, 'ajax', false);

		if ($isModal || ($isAjax !== false)) {

			if (is_string($isAjax))
				$options['method'] = $isAjax;

			if ($isAjax !== false) {
				if (isset($options['data']['confirm'])) {
					$options['data']['ajax-confirm'] = $options['data']['confirm'];
					unset($options['data']['confirm']);
				}
			}

			Html::addCssClass($options, $isModal ? 'modalButton' : 'ajaxButton');

			if (empty($options['value']) && !empty($url))
				$options['value'] = Url::to($url);

			$url = null;

			if (empty($options['title']))
				$options['title'] = $text;

		} else if (isset($options['method'])
			&& (strcasecmp($options['method'], 'get') != 0)
		) {
			return Html::beginForm($url, $options['method'], $options['form'] ?? null) //['csrf' => false])
				. Html::submitButton($text, $options['button'] ?? null)
				. Html::endForm();
		}

		$buttonMode = false;

		if (isset($options['class'])) {
			$class = $options['class'];
			if (is_array($class))
				$class = implode(',', $class);
			$class = ',' . $class . ',';
			$buttonMode = (strpos($class, ',btn,') !== false);
		}

		// if ($buttonMode) {
		// 	if (empty($url) == false) {
			//todo: handle url with onclick event
		// 	}
		// 	return parent::button($text, $options);
		// }

		return parent::a($text, $url, $options);
	}

	/*public static function a($text, $url = null, $options = [])
	{
		$token = 'aaa';



		if (empty($token) == false && ($url !== null)) {
			$url = Url::to($url);

			if ((str_starts_with($url, 'http://') == false)
				&& (str_starts_with($url, 'https://') == false))
			{
				//local



			}
		}

		return parent::a($text, $url, $options);
	}*/

	public static function pre($value, $options = [])
	{
		return Html::tag('pre', $value, $options);
	}
	public static function btn($value, $options = [])
	{
		return Html::tag('btn', $value, $options);
	}
	public static function div($value, $options = [])
	{
		return Html::tag('div', $value, $options);
	}
	public static function span($value, $options = [])
	{
		return Html::tag('span', $value, $options);
	}
	public static function p($value, $options = [])
	{
		return Html::tag('p', $value, $options);
	}

	public static function formErrorSummary($models, $options = [])
	{
		return static::span('', ['id' => 'errorspan']) . static::errorSummary($models, $options);
	}

	public static function activeSubmitButton($model, $caption = null, $options = [])
	{
		if (empty($caption))
			$caption = Yii::t('app', ($model->isNewRecord ?? true) ? 'Create' : 'Save Changes');
		else if (is_array($caption))
			$caption = $caption[$model->isNewRecord ?? true];

		// if (isset($options['done']))
			// shopack\base\widgets\ActiveForm::doneParam

		$options = array_replace_recursive([
			'class' => ['btn', 'btn-' . (($model->isNewRecord ?? true) ? 'success' : 'primary')],
		], $options);

		return Html::submitButton($caption, $options);
	}

	public static function formatRowDates(
		$createdAt, $createdBy,
		$updatedAt=null, $updatedBy=null,
		$removedAt=null, $removedBy=null
	) {
		$ret = [];

		if (empty($createdAt) == false) {
			$ret[] = 'ایجاد: ' .	Yii::$app->formatter->asJalaliWithTime($createdAt);
			if (!empty($createdBy))
				$ret[] = $createdBy->actorName;
		}

		if (empty($updatedAt) == false) {
			$ret[] = 'ویرایش: ' .	Yii::$app->formatter->asJalaliWithTime($updatedAt);
			if (!empty($updatedBy))
				$ret[] = $updatedBy->actorName;
		}

		if (empty($removedAt) == false) {
			$ret[] = 'حذف: ' .	Yii::$app->formatter->asJalaliWithTime($removedAt);
			if (!empty($removedBy))
				$ret[] = $removedBy->actorName;
		}

		return Html::tag('small', implode("<br>", $ret));
	}

	public static function splitAsList($value, $delimiter = '|', $ulOptions = null)
	{
		if (empty($value))
			return null;

		return self::ul(explode($delimiter, $value), $ulOptions);
	}

}
