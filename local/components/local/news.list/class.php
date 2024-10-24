<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class NewsListComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_TYPE'] = trim((string)($arParams['IBLOCK_TYPE'] ?? ''));
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['IBLOCK_CODE'] = trim((string)($arParams['IBLOCK_CODE'] ?? ''));
        $arParams['CACHE_TYPE'] ??= 'A';
        if (!in_array($arParams['CACHE_TYPE'],['A', 'Y', 'N'], true)) {
            $arParams['CACHE_TYPE'] = 'A';
        }
        $arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 36000000);
        if ($arParams['CACHE_TIME'] < 0) {
            $arParams['CACHE_TIME'] = 36000000;
        }

        return $arParams;
    }

    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->arResult['ITEMS'] = match (true) {
                $this->arParams['IBLOCK_ID'] !== 0 =>
                    $this->getElementByIblockId($this->arParams['IBLOCK_ID']),
                $this->arParams['IBLOCK_CODE'] !== '' =>
                    $this->getElementByIblockCode($this->arParams['IBLOCK_CODE']),
                $this->arParams['IBLOCK_TYPE'] !== '' =>
                    $this->getElementByIblockType($this->arParams['IBLOCK_TYPE']),
                default => array()
            };

            if (!$this->arResult['ITEMS']) {
                $this->abortResultCache();
                return;
            }

            $this->includeComponentTemplate();
        }
    }

    protected function getElementByIblockId($iblock_id)
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        if (is_null(IblockTable::getRow([
            'filter' => ['ID' => $iblock_id, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]))) {
            return array();
        }
        $rsElements = ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $iblock_id, 'ACTIVE' => 'Y']
        ]);
        $arElements[$iblock_id] = [];
        while ($element = $rsElements->fetch()) {
            $element['PREVIEW_PICTURE'] = \CFile::GetFileArray($element['PREVIEW_PICTURE']);
            $element['DETAIL_PICTURE'] = \CFile::GetFileArray($element['DETAIL_PICTURE']);

            $element['TIMESTAMP_X'] = $element['TIMESTAMP_X'] instanceof DateTime ?
                $element['TIMESTAMP_X']->toString() : '';
            $element['DATE_CREATE'] = $element['DATE_CREATE'] instanceof DateTime ?
                $element['DATE_CREATE']->toString() : '';
            $element['ACTIVE_FROM'] = $element['ACTIVE_FROM'] instanceof DateTime ?
                $element['ACTIVE_FROM']->toString() : '';
            $element['ACTIVE_TO'] = $element['ACTIVE_TO'] instanceof DateTime ?
                $element['ACTIVE_TO']->toString() : '';

            $arElements[$iblock_id][$element['ID']] = $element;
        }
        return $arElements;
    }

    protected function getElementByIblockCode($iblock_code)
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        $iblock_item = IblockTable::getRow([
            'filter' => ['CODE' => $iblock_code, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        if (is_null($iblock_item)) {
            return array();
        } else {
            return $this->getElementByIblockId($iblock_item['ID']);
        }
    }

    protected function getElementByIblockType($iblock_type)
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        $rsIblocks = IblockTable::getList([
            'filter' => ['IBLOCK_TYPE_ID' => $iblock_type, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        $arElements = [];
        while ($iblock = $rsIblocks->fetch()) {
            $arElements += $this->getElementByIblockId($iblock['ID']);
        }
        return $arElements;
    }
}
