<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class NewsListComponent extends \CBitrixComponent
{
    protected $arIblocks = [];
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
        $arParams['DETAIL_URL'] = trim((string)($arParams['DETAIL_URL'] ?? ''));
        $arParams['SECTION_URL'] = trim((string)($arParams['SECTION_URL'] ?? ''));
        $arParams['IBLOCK_URL'] = trim((string)($arParams['IBLOCK_URL'] ?? ''));
        $arParams['FILTER'] ??= array();
        if (!is_array($arParams['FILTER'])) {
            $arParams['FILTER'] = [];
        }
        $arParams['FILTER_CACHE'] ??= 'N';
        $arParams['FILTER_CACHE'] = $arParams['FILTER_CACHE'] === 'Y';
        $arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 36000000);
        if ($arParams['CACHE_TIME'] < 0) {
            $arParams['CACHE_TIME'] = 36000000;
        }

        return $arParams;
    }

    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    public function executeComponent()
    {
        if (!$this->arParams['FILTER_CACHE'] && $this->arParams['FILTER']) {
            $this->arParams['CACHE_TIME'] = 0;
        }

        if ($this->startResultCache()) {
            try {
                $this->checkModules('iblock');

                $this->initComponentArrays();

                $this->arResult = $this->getResultArray();

                $this->includeComponentTemplate();
            } catch (SystemException $e) {
                $this->abortResultCache();

                ShowError($e->getMessage());
            }
        }
    }

    protected function checkModules(...$modules)
    {
        foreach ($modules as $m) {
            if (!Loader::includeModule($m)) {
                throw new SystemException(Loc::getMessage('MODULE_NOT_FOUND'));
            }
        }
    }

    protected function initComponentArrays()
    {
        $this->initIblockArray();
        $this->initSectionArray();
    }

    protected function initIblockArray()
    {
        if (!$this->arParams['IBLOCK_ID'] && !$this->arParams['IBLOCK_CODE'] && !$this->arParams['IBLOCK_TYPE']) {
            throw new SystemException(Loc::getMessage('IBLOCK_FIELDS_EMPTY'));
        }

        if ($this->arParams['IBLOCK_ID'] > 0) {
            $iblock_id = $this->arParams['IBLOCK_ID'];
            if (!self::iblockExists($iblock_id)) {
                throw new SystemException(Loc::getMessage('IBLOCK_ID_NOT_VALID'));
            }
        } elseif ($this->arParams['IBLOCK_CODE']) {
            if (!$iblock_id = self::getIblockIdByCode($this->arParams['IBLOCK_CODE'])) {
                throw new SystemException(Loc::getMessage('IBLOCK_CODE_NOT_VALID'));
            }
        } else {
            $iblock_id = 0;
        }

        if ($iblock_id > 0) {
            $this->arIblocks = [$iblock_id];
        } elseif ($this->arParams['IBLOCK_TYPE']) {
            if (!self::iblockTypeExists($this->arParams['IBLOCK_TYPE'])) {
                throw new SystemException(Loc::getMessage('IBLOCK_TYPE_NOT_VALID'));
            }
            $this->arIblocks = self::getIblockByType($this->arParams['IBLOCK_TYPE']);
        }
    }

    protected function initSectionArray()
    {
        if ($this->arParams['SECTION_ID'] > 0) {
            $section_id = $this->arParams['SECTION_ID'];
            if (!self::sectionExists($section_id, $this->arIblocks)) {
                throw new SystemException(Loc::getMessage('SECTION_ID_NOT_VALID'));
            }
        } elseif ($this->arParams['SECTION_CODE']) {
            if (!$section_id = self::getSectionIdByCode($this->arParams['SECTION_CODE'])) {
                throw new SystemException(Loc::getMessage('SECTION_CODE_NOT_VALID'));
            }
        } else {
            $section_id = 0;
        }

        if ($section_id > 0) {
            $this->arSections = [$section_id];
            if ($this->arParams['INCLUDE_SUBSECTIONS']) {
                $this->arSections = array_merge($this->arSections, self::getSubsections($section_id));
            }
        } elseif ($this->arParams['INCLUDE_SUBSECTIONS']) {
            $this->arSections = array(); //if array is empty -> no section filter
        } else {
            $this->arSections = [false]; //root directory
        }
    }

    protected function getResultArray()
    {
        $arResult['ITEMS'] = $this->getResultItems();
        return $arResult;
    }

    protected function getResultItems()
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }

        $elements_filter = ['IBLOCK_ID' => $this->arIblocks, 'ACTIVE' => 'Y'];
        if ($this->arSections) {
            $elements_filter['IBLOCK_SECTION_ID'] = $this->arSections;
        }
        $elements_filter += $this->arParams['FILTER'];

        $element_select = [
            '*',
            'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL',
            'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
            'LIST_PAGE_URL' => 'IBLOCK.LIST_PAGE_URL'
        ];

        $rsElements = ElementTable::getList([
            'filter' => $elements_filter,
            'select' => $element_select
        ]);

        $arElements = [];
        foreach ($this->arIblocks as $iblock) {
            $arElements[$iblock] = [];
        }
        while ($element = $rsElements->fetch()) {
            $picture_keys = ['PREVIEW_PICTURE', 'DETAIL_PICTURE'];
            self::convertPictureToArray($element, $picture_keys);

            $date_keys = ['TIMESTAMP_X', 'DATE_CREATE', 'ACTIVE_FROM', 'ACTIVE_TO'];
            self::convertDateToString($element, $date_keys);

            self::convertElementUrl(
                $element,
                $this->arParams['DETAIL_URL'],
                $this->arParams['SECTION_URL'],
                $this->arParams['IBLOCK_URL']);

            $arElements[$element['IBLOCK_ID']][$element['ID']] = $element;
        }
        return $arElements;
    }

    public static function convertPictureToArray(&$array, $keys)
    {
        if (is_array($array) && is_array($keys)) {
            foreach ($keys as $k) {
                if (isset($array[$k])) {
                    $array[$k] = \CFile::GetFileArray($array[$k]);
                }
            }
        }
    }

    public static function convertDateToString(&$array, $keys)
    {
        if (is_array($array) && is_array($keys)) {
            foreach ($keys as $k) {
                if (isset($array[$k])) {
                    $array[$k] = $array[$k] instanceof DateTime ? $array[$k]->toString() : '';
                }
            }
        }
    }

    public static function convertElementUrl(&$element_array, $detail_url = '', $section_url = '', $iblock_url = '')
    {
        if (!Loader::includeModule('iblock')) {
            return;
        }

        if (isset($element_array['DETAIL_PAGE_URL'])) {
            if ($detail_url) {
                $element_array['DETAIL_PAGE_URL'] = $detail_url;
            }
            $element_array['DETAIL_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
                $element_array['DETAIL_PAGE_URL'],
                $element_array,
                false,
                'E'
            );
        }

        if (isset($element_array['SECTION_PAGE_URL'])) {
            if ($section_url) {
                $element_array['SECTION_PAGE_URL'] = $section_url;
            }
            $element_array['SECTION_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
                $element_array['SECTION_PAGE_URL'],
                $element_array,
                false,
                'E'
            );
        }

        if (isset($element_array['LIST_PAGE_URL'])) {
            if ($iblock_url) {
                $element_array['LIST_PAGE_URL'] = $iblock_url;
            }
            $element_array['LIST_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
                $element_array['LIST_PAGE_URL'],
                $element_array,
                false,
                'E'
            );
        }
    }

    public static function getIblockByType($iblock_type)
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        $rsIblocks = IblockTable::getList([
            'filter' => ['IBLOCK_TYPE_ID' => $iblock_type, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        $arIblocks = [];
        while ($iblock = $rsIblocks->fetch()) {
            $arIblocks[] = $iblock['ID'];
        }
        return $arIblocks;
    }

    public static function getSubsections($section_id)
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        $rsSections = SectionTable::getList([
            'filter' => ['IBLOCK_SECTION_ID' => $section_id, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        $arSections = [];
        while ($section = $rsSections->fetch()) {
            $arSections[] = $section['ID'];
            $arSections = array_merge($arSections, self::getSubsections($section['ID']));
        }
        return $arSections;
    }

    public static function iblockTypeExists($iblock_type)
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        return !is_null(TypeTable::getRow([
            'filter' => ['ID' => $iblock_type],
            'select' => ['ID']
        ]));
    }

    public static function iblockExists($iblock_id)
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        return !is_null(IblockTable::getRow([
            'filter' => ['ID' => $iblock_id, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]));
    }

    public static function sectionExists($section_id, $iblocks = '')
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        $filter = ['ID' => $section_id, 'ACTIVE' => 'Y'];
        if ($iblocks) {
            $filter['IBLOCK_ID'] = $iblocks;
        }
        return !is_null(SectionTable::getRow([
            'filter' => $filter,
            'select' => ['ID']
        ]));
    }

    public static function getIblockIdByCode($iblock_code)
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        $iblock_item = IblockTable::getRow([
            'filter' => ['CODE' => $iblock_code, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        if (is_null($iblock_item)) {
            return false;
        } else {
            return $iblock_item['ID'];
        }
    }

    public static function getSectionIdByCode($section_code)
    {
        if (!Loader::includeModule('iblock')) {
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
}
