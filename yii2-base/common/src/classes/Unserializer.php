<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\classes;

use yii\base\Component;
use shopack\base\common\classes\UnserializerInterface;

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
