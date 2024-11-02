<?php

namespace Test\Complexprop;

use Bitrix\Main\Localization\Loc;

class UserFieldComplexProperty extends \Bitrix\Main\UserField\Types\StringType
{
    public const USER_TYPE_ID = 'COMPLEX';

    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => Loc::getMessage('COMPLEXPROP_USERFIELD_NAME'),
			'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING
        ];
    }
}
