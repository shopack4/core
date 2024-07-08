<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use yii\web\ServerErrorHttpException;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\MessageModel;

class MessageSearchModel extends MessageModel
{
	use \shopack\base\common\db\SearchModelTrait;

	// public $msgUserID;

	// public function extraRules()
	// {
	// 	return [
	// 		[[
	// 			'msgUserID',
	// 		], 'number'],
	// 		[[
	// 			'msgUserID',
	// 		], 'default', 'value' => null],
	// 	];
	// }

	// public function attributeLabels()
	// {
	// 	return ArrayHelper::merge(parent::attributeLabels(), [
	// 		'msgUserID' => 'کاربر',
	// 	]);
	// }

	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function search($params)
	{
		$query = self::find();

		$dataProvider = new RestClientDataProvider([
			'query' => $query,
			'sort' => [
				// 'enableMultiSort' => true,
				'attributes' => [
					'msgID',
					// 'msgName',
					// 'msgStatus',
					'msgLastTryAt',
					'msgSentAt',
					'msgCreatedAt' => [
						'default' => SORT_DESC,
					],
					'msgCreatedBy',
					'msgUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'msgUpdatedBy',
					'msgRemovedAt' => [
						'default' => SORT_DESC,
					],
					'msgRemovedBy',
				],
				'defaultOrder' => [
					'msgCreatedAt' => SORT_DESC,
				],
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			throw new ServerErrorHttpException('Unknown error sh01');
			// $query->where('0=1');
			return $dataProvider;
		}

		if (empty($this->msgUserID) == false)
			$query->andWhere(['msgUserID' => $this->msgUserID]);
		else if (empty($params['msgUserID']) == false)
			$query->andWhere(['msgUserID' => $params['msgUserID']]);

		$this->applySearchValuesInQuery($query, $params);

		return $dataProvider;
	}

}
