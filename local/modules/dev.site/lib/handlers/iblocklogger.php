<?php

namespace Dev\Site\Handlers;

define('LOGGER_CODE', 'LOG');

\Bitrix\Main\Loader::includeModule('iblock');

class IblockLogger
{
    public static function OnAfterIBlockElementAddUpdateHandler(&$arFields)
    {
        if (!$element_iblock = self::getIBlockById($arFields['IBLOCK_ID'])) {
            return;
        }
        if ($element_iblock['CODE'] == LOGGER_CODE) {
            return;
        }
        if (!$logger_iblock = self::getIBlockByCode(LOGGER_CODE)) {
            return;
        }
        if (!$logger_section_id = self::createLoggerSection(
            $logger_iblock['ID'],
            $element_iblock['NAME'],
            $element_iblock['CODE']
            )) {
                return;
            }
    }

    public static function getIBlockByCode($iblock_code)
    {
        $rsIBlocks = \CIBlock::GetList(array(), ['=CODE' => $iblock_code]);
        return $rsIBlocks->Fetch();
    }

    public static function getIBlockById($iblock_id)
    {
        $rsIBlocks = \CIBlock::GetList(array(), ['=ID' => $iblock_id]);
        return $rsIBlocks->Fetch();
    }

    public static function createLoggerSection($logger_id, $iblock_name, $iblock_code)
    {
        $rsLoggerSections = \CIBlockSection::GetList(array(), [
            '=IBLOCK_ID' => $logger_id,
            '=NAME' => $iblock_name,
            '=CODE' => $iblock_code
        ], false, ['ID'], false);
        if ($logger_section_item = $rsLoggerSections->Fetch()) {
            return $logger_section_item['ID'];
        } else {
            $section_object = new \CIBlockSection;
            return $section_object->Add([
                'IBLOCK_ID' => $logger_id,
                'IBLOCK_SECTION_ID' => false,
                'NAME' => $iblock_name,
                'CODE' => $iblock_code
            ]);
        }
    }
}
