<?php

namespace Dev\Site\Agents;

use Bitrix\Main\Loader;
use Dev\Site\IBlock;

class IBlockLogger extends IBlock
{
    public static function clearOldLogs($logs_left_cnt = 10)
    {
        if (self::dev_is_int($logs_left_cnt) && $logs_left_cnt > 0) {
            $logs_cnt_validated = $logs_left_cnt;
        } else {
            $logs_cnt_validated = 10;
        }
        if (Loader::includeModule('iblock')) {
            if ($logger_iblock = self::getIBlockFieldsByCode(LOGGER_CODE)) {
                $rsElements = \CIBlockElement::GetList(
                    ['DATE_ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'],
                    ['IBLOCK_ID' => $logger_iblock['ID'], 'CHECK_PERMISSIONS' => 'N'],
                    false,
                    false,
                    ['ID', 'DATE_ACTIVE_FROM']
                );
                for ($i = 0; $element_item = $rsElements->GetNext(); $i++) {
                    if ($i >= $logs_cnt_validated) {
                        \CIBlockElement::Delete($element_item['ID']);
                    }
                }
                self::deleteEmptyIBlockSections($logger_iblock['ID']);
            }
        }

        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '(' . $logs_cnt_validated . ');';
    }
}
