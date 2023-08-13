<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;

class OnlinePaymentSearchModel extends OnlinePaymentModel
{
	use \shopack\base\common\db\SearchModelTrait;

	public $vchOwnerUserID;

	public function extraRules()
	{
		return [
			[[
				'vchOwnerUserID',
			], 'number'],
			[[
				'vchOwnerUserID',
			], 'default', 'value' => null],
		];
	}

	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'vchOwnerUserID' => 'مالک',
		]);
	}

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
					'onpID',
					'onpUUID',
					'onpKey',
					'onpGatewayID',
					'onpVoucherID',
					// 'onpAmount',
					'onpStatus',
					'onpCreatedAt' => [
						'asc'		=> ['onpCreatedAt' => SORT_ASC,		'onpID' => SORT_ASC],
						'desc'	=> ['onpCreatedAt' => SORT_DESC,	'onpID' => SORT_DESC],
						'default' => SORT_DESC,
					],
					'onpCreatedBy',
					'onpUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'onpUpdatedBy',
					'onpRemovedAt' => [
						'default' => SORT_DESC,
					],
					'onpRemovedBy',
				],
				'defaultOrder' => [
					'onpCreatedAt' => SORT_DESC,
				]
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		if (isset($this->vchOwnerUserID))
			$query->andWhere(['vchOwnerUserID' => $this->vchOwnerUserID]);
		else if (isset($params['vchOwnerUserID']))
			$query->andWhere(['vchOwnerUserID' => $params['vchOwnerUserID']]);

		$this->applySearchValuesInQuery($query);

		return $dataProvider;
	}

}
