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
            'TYPE' => 'CHECKBOX'
        ],
        'CACHE_TYPE' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'CACHE_TIME' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ]
    ]
];