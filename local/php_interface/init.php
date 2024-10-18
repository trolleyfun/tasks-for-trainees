<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

define('LOGGER_CODE', 'LOG');

function my_is_int($value)
{
    return strval($value) === strval(intval($value));
}

if (Loader::includeModule('dev.site')) {
    EventManager::getInstance()->addEventHandlerCompatible(
        'iblock',
        'OnAfterIBlockElementAdd',
        ['Dev\\Site\\Handlers\\IBlockLogger', 'OnAfterIBlockElementAddUpdateHandler']
    );

    EventManager::getInstance()->addEventHandlerCompatible(
        'iblock',
        'OnAfterIBlockElementUpdate',
        ['Dev\\Site\\Handlers\\IBlockLogger', 'OnAfterIBlockElementAddUpdateHandler']
    );
}
