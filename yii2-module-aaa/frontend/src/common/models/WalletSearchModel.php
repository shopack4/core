<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\WalletModel;

class WalletSearchModel extends WalletModel
{
	use \shopack\base\common\db\SearchModelTrait;

	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function search($params)
	{
		$query = self::find();

		// if ($params['justForMe'] ?? $_GET['justForMe'] ?? false)
		// 	$query->addUrlParameter('justForMe', 1);

		$dataProvider = new RestClientDataProvider([
			'query' => $query,
			'sort' => [
				// 'enableMultiSort' => true,
				'attributes' => [
					'walID',
					'walOwnerUserID',
					'walRemainedAmount',
					'walStatus',
					'walCreatedAt' => [
						'default' => SORT_DESC,
					],
					'walCreatedBy',
					'walUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'walUpdatedBy',
					'walRemovedAt' => [
						'default' => SORT_DESC,
					],
					'walRemovedBy',
				],
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		if (isset($params['walOwnerUserID']))
			$query->andWhere(['walOwnerUserID' => $params['walOwnerUserID']]);

		$this->applySearchValuesInQuery($query);

		return $dataProvider;
	}

}
