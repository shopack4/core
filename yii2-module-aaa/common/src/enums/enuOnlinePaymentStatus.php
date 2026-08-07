<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuOnlinePaymentStatus extends BaseEnum
{
    // -----------------------------------------------------
    // |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
    // | | | | |x| | | |x| | | | |x| |x| |x| | | | | | | | |
    // -----------------------------------------------------

    const New     = 'N';
    const Pending = 'P';
    const Paid    = 'I';
    const Error   = 'E';
    const Removed = 'R';

    public static $messageCategory = 'aaa';

    public static $list = [
        self::New     => 'New',
        self::Pending => 'Pending',
        self::Paid    => 'Paid',
        self::Error   => 'Error',
        self::Removed => 'Removed',
    ];
};
