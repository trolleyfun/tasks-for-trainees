<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class NewsListComponent extends \CBitrixComponent
{
    protected $arSections = [];

    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_TYPE'] = trim((string)($arParams['IBLOCK_TYPE'] ?? ''));
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['IBLOCK_CODE'] = trim((string)($arParams['IBLOCK_CODE'] ?? ''));
        $arParams['SECTION_ID'] = (int)($arParams['SECTION_ID'] ?? 0);
        $arParams['SECTION_CODE'] = trim((string)($arParams['SECTION_CODE'] ?? ''));
        $arParams['INCLUDE_SUBSECTIONS'] ??= 'Y';
        $arParams['INCLUDE_SUBSECTIONS'] = $arParams['INCLUDE_SUBSECTIONS'] !== 'N';
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
            $section_id = $this->getSectionIdByParams();
            if ($section_id < 0 || $section_id > 0 && !$this->sectionExists($section_id)) {
                $this->abortResultCache();
                return;
            }

            if ($section_id > 0) {
                $this->arSections = [$section_id];
                if ($this->arParams['INCLUDE_SUBSECTIONS']) {
                    $this->getSubsections($section_id);
                }
            } elseif ($this->arParams['INCLUDE_SUBSECTIONS']) {
                $this->arSections = array(); //if array is empty -> no section filter
            } else {
                $this->arSections = [false];
            }

            $this->arResult['ITEMS'] = match (true) {
                $this->arParams['IBLOCK_ID'] > 0 =>
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
        if (!$this->iblockExists($iblock_id)) {
            return array();
        }
        $elements_filter = ['IBLOCK_ID' => $iblock_id, 'ACTIVE' => 'Y'];
        if ($this->arSections) {
            $elements_filter['IBLOCK_SECTION_ID'] = $this->arSections;
        }
        $rsElements = ElementTable::getList([
            'filter' => $elements_filter
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

    protected function getSubsections($section_id)
    {
        if (!$this->sectionExists($section_id) || !Loader::includeModule('main')) {
            return;
        }
        $rsSections = SectionTable::getList([
            'filter' => ['IBLOCK_SECTION_ID' => $section_id, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        while ($section = $rsSections->fetch()) {
            $this->arSections[] = $section['ID'];
            $this->getSubsections($section['ID']);
        }
    }

    protected function sectionExists($section_id)
    {
        if (!Loader::includeModule('main')) {
            return false;
        }
        return !is_null(SectionTable::getRow([
            'filter' => ['ID' => $section_id, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]));
    }

    protected function iblockExists($iblock_id)
    {
        if (!Loader::includeModule('main')) {
            return false;
        }
        return !is_null(IblockTable::getRow([
            'filter' => ['ID' => $iblock_id, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]));
    }

    protected function getSectionIdByCode($section_code)
    {
        if (!Loader::includeModule('main')) {
            return false;
        }
        $section_item = SectionTable::getRow([
            'filter' => ['CODE' => $section_code, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        if (is_null($section_item)) {
            return false;
        } else {
            return $section_item['ID'];
        }
    }

    protected function getSectionIdByParams()
    {
        if ($this->arParams['SECTION_ID'] > 0) {
            $section_id = $this->arParams['SECTION_ID'];
        } elseif ($this->arParams['SECTION_CODE']) {
            $section_id = $this->getSectionIdByCode($this->arParams['SECTION_CODE']);
            if (!$section_id) {
                $section_id = -1;
            }
        } else {
            $section_id = 0;
        }
        return $section_id;
    }
}
