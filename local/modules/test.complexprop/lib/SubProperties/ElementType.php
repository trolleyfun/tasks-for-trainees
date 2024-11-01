<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

class ElementType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_ELEMENTTYPE_NAME');
    }

    public function getPropertyFieldHtml($value, array $strHTMLControlName): string
    {
        return '';
    }
}
