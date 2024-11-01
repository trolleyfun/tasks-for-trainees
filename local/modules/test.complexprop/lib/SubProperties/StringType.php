<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

class StringType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_STRINGTYPE_NAME');
    }

    public function getPropertyFieldHtml($value, array $strHTMLControlName): string
    {
        return '';
    }
}
