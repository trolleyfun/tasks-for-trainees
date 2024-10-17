<?php

namespace Dev\Site\Agents;

use Bitrix\Main\Loader;
use Dev\Site\IBlock;

define('LOGGER_CODE', 'LOG');

class IBlockLogger
{
    public static function clearOldLogs()
    {
        if (Loader::includeModule('iblock')) {
            if ($logger_iblock = IBlock::getIBlockFieldsByCode(LOGGER_CODE)) {
                $rsElements = \CIBlockElement::GetList(
                    ['DATE_ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'],
                    ['=IBLOCK_ID' => $logger_iblock['ID']],
                    false,
                    false,
                    ['ID', 'DATE_ACTIVE_FROM']
                );
                for ($i = 0; $element_item = $rsElements->GetNext(); $i++) {
                    if ($i > 9) {
                        \CIBlockElement::Delete($element_item['ID']);
                    }
                }
            }
        }

        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
