<?php

use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!Loader::includeModule('iblock')) {
    die();
}

$arComponentParameters = [
    'GROUPS' => [],
    'PARAMETERS' => [
        'IBLOCK_TYPE' => [
            'TYPE' => 'STRING'
        ],
        'IBLOCK_ID' => [
            'TYPE' => 'STRING'
        ],
        'IBLOCK_CODE' => [
            'TYPE' => 'STRING'
        ]
    ]
];
