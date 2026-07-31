<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use yii\web\ServerErrorHttpException;
use shopack\base\frontend\common\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\UploadQueueModel;

class UploadQueueSearchModel extends UploadQueueModel
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
                    'uquID',
                    'uquUUID',
                    'uquStatus',
                    'uquCreatedAt' => [
                        'asc'     => ['uquCreatedAt' => SORT_ASC,  'uquID' => SORT_ASC],
                        'desc'    => ['uquCreatedAt' => SORT_DESC, 'uquID' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                    'uquCreatedBy',
                    'uquUpdatedAt' => [
                        'default' => SORT_DESC,
                    ],
                    'uquUpdatedBy',
                    'uquRemovedAt' => [
                        'default' => SORT_DESC,
                    ],
                    'uquRemovedBy',
                ],
                'defaultOrder' => [
                    'uquCreatedAt' => SORT_DESC,
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

        $this->applySearchValuesInQuery($query, $params);

        return $dataProvider;
    }
}
