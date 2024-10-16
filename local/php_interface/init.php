<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::includeModule('dev.site');

EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnAfterIBlockElementAdd',
    ['Dev\\Site\\Handlers\\IBlockLogger', 'OnAfterIBlockElementAddUpdateHandler']
);

EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    ['Dev\\Site\\Handlers\\IBlockLogger', 'OnAfterIBlockElementAddUpdateHandler']
);
