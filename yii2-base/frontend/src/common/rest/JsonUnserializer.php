<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\rest;

use shopack\base\common\helpers\Json;
use shopack\base\frontend\common\rest\Unserializer;

/**
 * Class JsonUnserializer
 *
 * @package shopack\base\frontend\common\rest
 */
class JsonUnserializer extends Unserializer
{
  /**
   * @param string $data
   * @param bool $asArray
   * @return mixed
   */
  public function unserialize($data, $asArray = true)
  {
    return Json::decode($data, $asArray);
  }

}
