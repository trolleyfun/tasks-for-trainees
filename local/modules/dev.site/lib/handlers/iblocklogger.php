<?php

namespace Dev\Site\Handlers;

define('LOGGER_CODE', 'LOG');

\Bitrix\Main\Loader::includeModule('iblock');

class IblockLogger
{
    public static function OnAfterIBlockElementAddUpdateHandler(&$arFields)
    {
        if (!$arFields['RESULT']) {
            return;
        }
        if (!$element_iblock = self::getIBlockFieldsById($arFields['IBLOCK_ID'])) {
            return;
        }
        if ($element_iblock['CODE'] == LOGGER_CODE) {
            return;
        }
        if (!$logger_iblock = self::getIBlockFieldsByCode(LOGGER_CODE)) {
            return;
        }
        if (!$element_fields = self::getElementFieldsById($arFields['ID'])) {
            return;
        }
        if (!$logger_section_id = self::getLoggerSectionId(
            $logger_iblock['ID'],
            $element_iblock['NAME'],
            $element_iblock['CODE']
            )) {
            return;
        }

        $element_object = new \CIBlockElement;
        $element_object->Add([
            'IBLOCK_ID' => $logger_iblock['ID'],
            'IBLOCK_SECTION_ID' => $logger_section_id,
            'NAME' => $arFields['ID'],
            'DATE_ACTIVE_FROM' => $element_fields['TIMESTAMP_X']
        ]);
    }

    public static function getIBlockFieldsByCode($iblock_code)
    {
        $rsIBlocks = \CIBlock::GetList(array(), ['=CODE' => $iblock_code]);
        return $rsIBlocks->GetNext();
    }

    public static function getIBlockFieldsById($iblock_id)
    {
        $rsIBlocks = \CIBlock::GetList(array(), ['=ID' => $iblock_id]);
        return $rsIBlocks->GetNext();
    }

    public static function getElementFieldsById($element_id)
    {
        $rsElements = \CIBlockElement::GetByID($element_id);
        return $rsElements->GetNext();
    }

    public static function getLoggerSectionId($logger_id, $section_name, $section_code)
    {
        $rsLoggerSections = \CIBlockSection::GetList(array(), [
            '=IBLOCK_ID' => $logger_id,
            '=NAME' => $section_name,
            '=CODE' => $section_code
        ], false, ['ID'], false);
        if ($logger_section_item = $rsLoggerSections->Fetch()) {
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
