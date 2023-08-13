<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\WalletTransactionModel;

class WalletTransactionSearchModel extends WalletTransactionModel
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
					'wtrID',
					'wtrWalletID',
					'wtrVoucherID',
					'wtrOnlinePaymentID',
					'wtrOfflinePaymentID',
					'wtrAmount',
					'wtrStatus',
					'wtrCreatedAt' => [
						'asc'		=> ['wtrCreatedAt' => SORT_ASC,		'wtrID' => SORT_ASC],
						'desc'	=> ['wtrCreatedAt' => SORT_DESC,	'wtrID' => SORT_DESC],
						'default' => SORT_DESC,
					],
					'wtrCreatedBy',
					'wtrUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'wtrUpdatedBy',
					'wtrRemovedAt' => [
						'default' => SORT_DESC,
					],
					'wtrRemovedBy',
				],
				'defaultOrder' => [
					'wtrCreatedAt' => SORT_DESC,
				]
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
