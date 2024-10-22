<?php

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!Loader::includeModule('iblock')) {
    die();
}

$arIblockTypes = TypeTable::getList(['select' => ['ID', 'NAME']])->fetchAll();
$iblock_types = [];
foreach ($arIblockTypes as $type) {
    $iblock_types[$type['ID']] = $type['NAME'];
}

$arComponentParameters = [
    'GROUPS' => [],
    'PARAMETERS' => [
        'IBLOCK_TYPE' => [
            'TYPE' => 'LIST',
            'MULTIPLE' => 'N',
            'ADDITIONAL_VALUES' => 'N',
            'VALUES' => $iblock_types
        ],
        'IBLOCK_ID' => [
            'TYPE' => 'STRING'
        ],
        'IBLOCK_CODE' => [
            'TYPE' => 'STRING'
        ]
    ]
];
