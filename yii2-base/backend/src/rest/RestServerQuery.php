<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\rest;

use Yii;

class RestServerQuery extends \yii\db\ActiveQuery
{
  public $translate = false;
  public function i18nTranslate($translate)
  {
    $this->translate = $translate;

    return $this;
  }

  public function applyI18NTranslate()
  {
    if ($this->translate == false)
      return;

    $modelClass = $this->modelClass;

    //-- languages ------------------------------------
    $languages = [];

    //sample: 'en-US,en;q=0.9,fa;q=0.8'
    $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
    if ((empty($acceptLanguage) == false) && ($acceptLanguage != '*')) {
      $acceptLanguages = explode(',', $acceptLanguage);
      foreach ($acceptLanguages as $lng)
      {
        $parts = explode(';', $lng);
        $languages[] = $parts[0];
      }
    } else
      $languages[] = YII::$app->language ?? 'en';

    $languages = array_unique($languages);

    $lngParts = [];
    foreach ($languages as $lng)
    {
      $lng = explode('_', str_replace('-', '_', $lng));

      $lng[0] = strtolower($lng[0]);

      if (isset($lng[1])) {
        $lng[1] = strtoupper($lng[1]);
        $lngs = implode('_', $lng);
        $lngParts[] = "JSON_UNQUOTE(JSON_EXTRACT(__dataField__, '$.{$lngs}.__field__'))";
      }

      $lngParts[] = "JSON_UNQUOTE(JSON_EXTRACT(__dataField__, '$.{$lng[0]}.__field__'))";
    }
    $lngParts = implode(',', $lngParts);

    //--------------------------------------
    foreach ($modelClass::$i18nDataFields as $dataField => $fields)
    {
      foreach ($fields as $field)
      {
        if (isset($this->select["{$field}"]))
          unset($this->select["{$field}"]);

        $this->addSelect(new \yii\db\Expression("COALESCE("
          . strtr($lngParts, [
            '__dataField__' => $dataField,
            '__field__' => $field,
          ])
          . ", {$field}) AS {$field}"));

        // unset($query->select["{$field}_translated"]);
        // $query->addSelect(new \yii\db\Expression("COALESCE(JSON_UNQUOTE(JSON_EXTRACT({$dataField}, '$.{$language}.{$field}')), {$field}) AS {$field}_translated"));
      }
    }
  }

  public function createCommand($db = null)
  {
    $this->applyI18NTranslate();

    return parent::createCommand($db);
  }

  public function prepare($builder)
  {
    $modelClass = $this->modelClass;
    $model = $modelClass::instance();

    $statusColumnName = $model->getStatusColumnName();
		if ($statusColumnName) {
      $pkCount = 0;
      $pkFound = 0;
      $statusFound = false;

      if (empty($this->where) == false) {
        $db = $modelClass::getDb();
        $params = [];
        $where = $db->getQueryBuilder()->buildCondition($this->where, $params);

        if (strpos($where, $statusColumnName) === false) {
          $pks = $modelClass::primaryKey();
          if (empty($pks) == false) {
            $pks = (array)$pks;

            $pkCount = count($pks);
            $pkFound = 0;

            foreach ($pks as $pk) {
              if (strpos($where, $pk) !== false)
                ++$pkFound;
            }
          }
        } else {
          $statusFound = true;
        }
      }

      if (($statusFound == false) && (($pkFound == 0) || ($pkFound < $pkCount)))
        $this->andWhere(['!=', $statusColumnName, 'R']); //::Removed
		}

    $query = parent::prepare($builder);
    return $query;
  }

  public function addFileUrl($fullFileUrlParamName, $fileRelationName = null)
  {
    if (empty($fileRelationName) == false) {
      $this->joinWith("{$fileRelationName} f{$fileRelationName}");
      $myTableName = "f{$fileRelationName}";
    } else {
      $fileRelationName = 'file';
      $myTableName = 'tbl_AAA_UploadFile';
    }

    $this
      ->leftJoin("(
        SELECT ROW_NUMBER() OVER(PARTITION BY uquFileID ORDER BY RAND()) AS row_num
             , uquFileID
             , uquGatewayID
          FROM tbl_AAA_UploadQueue
         WHERE uquStatus = 'S'
               ) AS q{$fileRelationName}",
        "q{$fileRelationName}.uquFileID = {$myTableName}.uflID AND q{$fileRelationName}.row_num = 1")
      ->leftJoin("tbl_AAA_Gateway g{$fileRelationName}", "g{$fileRelationName}.gtwID = q{$fileRelationName}.uquGatewayID")
      ->addSelect(new \yii\db\Expression(<<<SQL
CASE JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.type'))
  WHEN 's3' THEN
    CASE WHEN IFNULL(JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.EndpointIsVirtualHosted')), 0)
      THEN CONCAT(
        IF(
          LEFT(JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.endpoint')), 5) = 'http:',
          'http://',
          'https://'
        ),
        JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.bucket')),
        '.',
        IF(
          SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.endpoint')), 5, 1) = ':',
          SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.endpoint')), 8),
          SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.endpoint')), 9)
        ),
        '/',
        {$myTableName}.uflPath,
        '/',
        {$myTableName}.uflStoredFileName
      )
      ELSE CONCAT(
        JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.endpoint')),
        '/',
        JSON_UNQUOTE(JSON_EXTRACT(g{$fileRelationName}.gtwPluginParameters, '$.bucket')),
        '/',
        {$myTableName}.uflPath,
        '/',
        {$myTableName}.uflStoredFileName
      )
    END
  WHEN 'nfs' THEN
    'NFS PATH'
  ELSE NULL
END AS {$fullFileUrlParamName}
SQL
      ))
    ;

    return $this;
  }

}
