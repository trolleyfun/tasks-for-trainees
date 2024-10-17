<?php

namespace Dev\Site\Agents;

define('LOGGER_CODE', 'LOG');

class IblockLogger
{
    public static function clearOldLogs()
    {
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
