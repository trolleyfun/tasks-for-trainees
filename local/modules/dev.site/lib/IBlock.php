<?php

namespace Dev\Site;

use Bitrix\Main\Loader;

class IBlock
{
    public static function getIBlockFieldsByCode($iblock_code)
    {
        if (!$iblock_code || !Loader::includeModule('iblock')) {
            return false;
        } else {
            return \CIBlock::GetList(
                array(),
                ['CODE' => $iblock_code, 'CHECK_PERMISSIONS' => 'N']
            )->GetNext();
        }
    }

    public static function getIBlockFieldsById($iblock_id)
    {
        if (!$iblock_id || !Loader::includeModule('iblock')) {
            return false;
        } else {
            return \CIBlock::GetList(
                array(),
                ['ID' => $iblock_id, 'CHECK_PERMISSIONS' => 'N']
            )->GetNext();
        }
    }

    public static function getElementFieldsById($element_id)
    {
        if (!$element_id || !Loader::includeModule('iblock')) {
            return false;
        } else {
            return \CIBlockElement::GetList(
                array(),
                ['ID' => $element_id, 'CHECK_PERMISSIONS' => 'N']
            )->GetNext();
        }
    }

    public static function getElementPath($element_name, $section_id, $iblock_name)
    {
        if (!$section_id) {
            return [$iblock_name, $element_name];
        } else {
            if (!$section_path = self::getSectionPath($section_id)) {
                return false;
            } else {
                $section_path[] = $element_name;
                return $section_path;
            }
        }
    }

    public static function getSectionPath($section_id)
    {
        if (!$section_id || !Loader::includeModule('iblock')) {
            return false;
        }
        $section_item = \CIBlockSection::GetList(
            array(),
            ['ID' => $section_id, 'CHECK_PERMISSIONS' => 'N'],
            false,
            ['ID', 'NAME', 'IBLOCK_ID', 'IBLOCK_SECTION_ID']
        )->GetNext();
        if (!$section_item) {
            return false;
        } else {
            if ($section_item['IBLOCK_SECTION_ID']) {
                $parent_section_path = self::getSectionPath($section_item['IBLOCK_SECTION_ID']);
            } else {
                if (!$section_iblock = self::getIBlockFieldsById($section_item['IBLOCK_ID'])) {
                    return false;
                }
                $parent_section_path = [$section_iblock['NAME']];
            }

            if (!$parent_section_path) {
                return false;
            } else {
                $parent_section_path[] = $section_item['NAME'];
                return $parent_section_path;
            }
        }
    }

    public static function deleteEmptyIBlockSections($iblock_id)
    {
        if (!$iblock_id || !Loader::includeModule('iblock')) {
            return;
        }
        $rsSections = \CIBlockSection::GetList(
            array(),
            ['IBLOCK_ID' => $iblock_id, 'CHECK_PERMISSIONS' => 'N'],
            true,
            ['ID']
        );
        while ($section_item = $rsSections->GetNext()) {
            if (!$section_item['ELEMENT_CNT']) {
                \CIBlockSection::Delete($section_item['ID'], false);
            }
        }
    }
}
