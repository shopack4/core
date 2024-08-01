<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\widgets\grid;

class ExpandRowColumn extends \kartik\grid\ExpandRowColumn
{
	public $expandOneOnly = true;
	public $allowBatchToggle = false;
	public $detailAnimationDuration = 150;
	public $detailRowCssClass = 'table-default';

}
