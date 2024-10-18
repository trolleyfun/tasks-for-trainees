<?php

namespace Dev\Site\Handlers;

use Bitrix\Main\Loader;
use Dev\Site\IBlock;

class IblockLogger
{
    public static function OnAfterIBlockElementAddUpdateHandler(&$arFields)
    {
        if (!$arFields['RESULT'] || !Loader::includeModule('iblock')) {
            return;
        }
        if (!$element_iblock = IBlock::getIBlockFieldsById($arFields['IBLOCK_ID'])) {
            return;
        }
        if ($element_iblock['CODE'] == LOGGER_CODE) {
            return;
        }
        if (!$logger_iblock = IBlock::getIBlockFieldsByCode(LOGGER_CODE)) {
            return;
        }
        if (!$element_fields = IBlock::getElementFieldsById($arFields['ID'])) {
            return;
        }
        if (!$logger_section_id = self::getLoggerSectionId(
            $logger_iblock['ID'],
            $element_iblock['NAME'],
            $element_iblock['CODE']
        )) {
            return;
        }
        if (!$arPath = IBlock::getElementPath(
            $element_fields['NAME'],
            $element_fields['IBLOCK_SECTION_ID'],
            $element_iblock['NAME']
        )) {
            return;
        }

        $element_object = new \CIBlockElement;
        $element_object->Add([
            'IBLOCK_ID' => $logger_iblock['ID'],
            'IBLOCK_SECTION_ID' => $logger_section_id,
            'NAME' => $arFields['ID'],
            'DATE_ACTIVE_FROM' => $element_fields['TIMESTAMP_X'],
            'PREVIEW_TEXT_TYPE' => 'text',
            'PREVIEW_TEXT' => implode(' -> ', $arPath)
        ]);
    }

    public static function getLoggerSectionId($logger_id, $section_name, $section_code)
    {
        if (!$logger_id || !$section_name || !$section_code) {
            return false;
        }
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        $logger_section_item = \CIBlockSection::GetList(array(), [
            'IBLOCK_ID' => $logger_id,
            'NAME' => $section_name,
            'CODE' => $section_code,
            'CHECK_PERMISSIONS' => 'N'
        ], false, ['ID'])->GetNext();
        if ($logger_section_item) {
            return $logger_section_item['ID'];
        } else {
            $section_object = new \CIBlockSection;
            return $section_object->Add([
                'IBLOCK_ID' => $logger_id,
                'IBLOCK_SECTION_ID' => false,
                'NAME' => $section_name,
                'CODE' => $section_code
            ]);
        }
    }
}
