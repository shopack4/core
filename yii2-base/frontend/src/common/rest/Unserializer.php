<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\rest;

use yii\base\Component;
use shopack\base\frontend\common\rest\UnserializerInterface;

/**
 * Class Unserializer
 *
 * @package shopack\base\frontend\common\rest
 */
abstract class Unserializer extends Component implements UnserializerInterface
{
  /**
   * @inheritdoc
   */
  public function unserialize($data, $asArray = true)
  {
    return $asArray ? (array) $data : $data;
  }

}
