<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS' => [],
    'PARAMETERS' => [
        'IBLOCK_TYPE' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'IBLOCK_ID' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'IBLOCK_CODE' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'SECTION_ID' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'SECTION_CODE' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'INCLUDE_SUBSECTIONS' => [
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y'
        ],
        'FILTER' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'Y'
        ],
        'FILTER_CACHE' => [
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N'
        ],
        'CACHE_TYPE' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'A'
        ],
        'CACHE_TIME' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N',
            'DEFAULT' => '36000000'
        ]
    ]
];
