<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\base;

use Yii;
use shopack\base\common\helpers\Json;

trait ApplicationInstanceIDTrait
{
	public function getInstanceID()
	{
		$path = Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'params-local.json';
		$content = [];
		if (file_exists($path))
			$content = Json::decode(file_get_contents($path));

		$instanceID = $content['instanceID'] ?? null;
		if (empty($instanceID)) {
			$instanceID = Yii::$app->id . '-' . uniqid(true);
			$content['instanceID'] = $instanceID;
			file_put_contents($path, Json::encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		}

		return $instanceID;
	}

}
