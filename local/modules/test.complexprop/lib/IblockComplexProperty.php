<?php

namespace Test\Complexprop;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class IblockComplexProperty
{
    public static function GetUserTypeDescription()
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        return [
            'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
            'USER_TYPE' => 'COMPLEX',
            'DESCRIPTION' => Loc::getMessage('COMPLEXPROP_IBLOCK_DESCRIPTION'),
            'ConvertToDB' => [__CLASS__, 'ConverToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB']
        ];
    }

    public static function ConvertToDB($arProperty, $value)
    {

    }

    public static function ConvertFromDB($arProperty, $value)
    {

    }
}
