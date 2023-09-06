<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use yii\web\ServerErrorHttpException;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\VoucherModel;

class VoucherSearchModel extends VoucherModel
{
	use \shopack\base\common\db\SearchModelTrait;

	// public $vchOwnerUserID;

	// public function attributeLabels()
	// {
	// 	return ArrayHelper::merge(parent::attributeLabels(), [
	// 		'usrssnLoginDateTime' => 'آخرین ورود',
	// 		'loginDateTime' => 'آخرین ورود',
	// 		'online' => 'آنلاین',
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
					'vchID',
					'vchOwnerUserID',
					'vchAmount',
					'vchStatus',
					'vchCreatedAt' => [
						'asc'		=> ['vchCreatedAt' => SORT_ASC,		'vchID' => SORT_ASC],
						'desc'	=> ['vchCreatedAt' => SORT_DESC,	'vchID' => SORT_DESC],
						'default' => SORT_DESC,
					],
					'vchCreatedBy',
					'vchUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'vchUpdatedBy',
					'vchRemovedAt' => [
						'default' => SORT_DESC,
					],
					'vchRemovedBy',
				],
				'defaultOrder' => [
					'vchCreatedAt' => SORT_DESC,
				]
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			throw new ServerErrorHttpException('Unknown error sh01');
			// $query->where('0=1');
			return $dataProvider;
		}

		if (empty($this->vchOwnerUserID) == false)
			$query->andWhere(['vchOwnerUserID' => $this->vchOwnerUserID]);
		else if (empty($params['vchOwnerUserID']) == false)
			$query->andWhere(['vchOwnerUserID' => $params['vchOwnerUserID']]);

		$this->applySearchValuesInQuery($query);

		return $dataProvider;
	}

}
