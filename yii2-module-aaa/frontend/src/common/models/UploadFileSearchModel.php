<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\UploadFileModel;

class UploadFileSearchModel extends UploadFileModel
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

		$dataProvider = new RestClientDataProvider([
			'query' => $query,
			'sort' => [
				// 'enableMultiSort' => true,
				'attributes' => [
					'uflID',
					'uflUUID',
					'uflStatus',
					'uflCreatedAt' => [
						'asc'		=> ['uflCreatedAt' => SORT_ASC,		'uflID' => SORT_ASC],
						'desc'	=> ['uflCreatedAt' => SORT_DESC,	'uflID' => SORT_DESC],
						'default' => SORT_DESC,
					],
					'uflCreatedBy',
					'uflUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'uflUpdatedBy',
					'uflRemovedAt' => [
						'default' => SORT_DESC,
					],
					'uflRemovedBy',
				],
				'defaultOrder' => [
					'uflCreatedAt' => SORT_DESC,
				]
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		if (empty($this->uflOwnerUserID) == false)
			$query->andWhere(['uflOwnerUserID' => $this->uflOwnerUserID]);
		else if (empty($params['uflOwnerUserID']) == false)
			$query->andWhere(['uflOwnerUserID' => $params['uflOwnerUserID']]);

		$this->applySearchValuesInQuery($query);

		return $dataProvider;
	}

}
