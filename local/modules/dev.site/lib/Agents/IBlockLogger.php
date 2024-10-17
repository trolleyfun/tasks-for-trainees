<?php

namespace Dev\Site\Agents;

use Bitrix\Main\Loader;
use Dev\Site\IBlock;

define('LOGGER_CODE', 'LOG');

class IBlockLogger
{
    public static function clearOldLogs($logs_left_cnt = 10)
    {
        if (Loader::includeModule('iblock')) {
            if ($logger_iblock = IBlock::getIBlockFieldsByCode(LOGGER_CODE)) {
                $rsElements = \CIBlockElement::GetList(
                    ['DATE_ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'],
                    ['IBLOCK_ID' => $logger_iblock['ID'], 'CHECK_PERMISSIONS' => 'N'],
                    false,
                    false,
                    ['ID', 'DATE_ACTIVE_FROM']
                );
                for ($i = 0; $element_item = $rsElements->GetNext(); $i++) {
                    if ($i >= $logs_left_cnt) {
                        \CIBlockElement::Delete($element_item['ID']);
                    }
                }
                IBlock::deleteEmptyIBlockSections($logger_iblock['ID']);
            }
        }

        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '(' . $logs_left_cnt . ');';
    }
}
