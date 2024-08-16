<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\db;

use Yii;

class Connection extends \yii\db\Connection
{
	public $utcNow;

	public function init()
	{
		parent::init();

    $this->on(static::EVENT_AFTER_OPEN, [$this, 'slotAfterOpen']);
	}

	public function slotAfterOpen() //$event)
	{
		// $this->setTimeZoneToUtc();
		$this->syncTimeZoneFromCaller();
	}

  protected function setTimeZoneToUtc()
	{
		$this->createCommand("SET time_zone = '+00:00'")->execute();

		$qry = "SELECT UTC_TIMESTAMP(6) as _now;";
		$result = $this->createCommand($qry)->queryOne();
		$this->utcNow = new \DateTimeImmutable($result['_now'], new \DateTimeZone('UTC'));
	}

  protected function syncTimeZoneFromCaller()
	{
		if (empty(Yii::$app->formatter->timeZone)) {
			$timeZone = '+00:00';
		} else {
			$timeZone = Yii::$app->formatter->timeZone;
			if (stripos($timeZone, 'GMT') === 0)
				$timeZone = substr($timeZone, 3);
		}

		$this->createCommand("SET time_zone = '" . $timeZone . "'")->execute();

		$qry = "SELECT NOW() as _now;";
		$result = $this->createCommand($qry)->queryOne();
		$this->utcNow = new \DateTimeImmutable($result['_now'], new \DateTimeZone($timeZone));
	}

}
