<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\grid;

class GridView extends \kartik\grid\GridView
{
	public $export = false;
	public $filterOnFocusOut = false;
	public $filterPosition = self::FILTER_POS_HEADER;
}
