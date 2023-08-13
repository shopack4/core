<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

$this->title = Yii::t('aaa', 'Shopping Card');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="shopping-card-view w-100">
  <div class='card border-default'>
    <div class='card-body text-center'><?= Yii::t('aaa', 'Shopping card is empty') ?></div>
  </div>
</div>
