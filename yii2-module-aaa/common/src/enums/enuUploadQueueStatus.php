<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUploadQueueStatus extends BaseEnum
{
    // -----------------------------------------------------
    // |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
    // |x|x| | |x| | | | | | | | |x| | | |x|x| | | | | | | |
    // -----------------------------------------------------

    const New        = 'N';
    const FirstTry   = 'A';
    const SecondTry  = 'B';
    const Stored     = 'S';
    const Error      = 'E';
    const Removed    = 'R';

    public static $messageCategory = 'aaa';

    public static $list = [
        self::New        => 'New',
        self::FirstTry   => 'First Try',
        self::SecondTry  => 'Second Try',
        self::Stored     => 'Stored',
        self::Error      => 'Error',
        self::Removed    => 'Removed',
    ];
};
