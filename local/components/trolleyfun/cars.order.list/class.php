<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Dev\Site\Exceptions\ComponentException;

class CarsOrderListComponent extends \CBitrixComponent
{
    protected $userId;

    protected $timeFrom;

    protected $timeTo;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['CARS_IBLOCK_CODE'] = trim(((string)$arParams['CARS_IBLOCK_CODE'] ?? ''));
        $arParams['ORDERS_IBLOCK_CODE'] = trim(((string)$arParams['ORDERS_IBLOCK_CODE'] ?? ''));
        $arParams['JOBS_IBLOCK_CODE'] = trim(((string)$arParams['JOBS_IBLOCK_CODE'] ?? ''));
        $arParams['TIME_FROM_ALIAS'] = trim(((string)$arParams['TIME_FROM_ALIAS'] ?? ''));
        $arParams['TIME_TO_ALIAS'] = trim(((string)$arParams['TIME_TO_ALIAS'] ?? ''));

        return $arParams;
    }

    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    public function executeComponent()
    {
        try {
            $this->checkModules('iblock');

            $this->initComponentProperties();
        } catch (ComponentException $e) {
            ShowError($e->getMessage());
        }
    }

    protected function checkModules(...$modules)
    {
        foreach ($modules as $m) {
            if (!Loader::includeModule($m)) {
                throw new ComponentException(Loc::getMessage('MODULE_NOT_FOUND'));
            }
        }
    }

    protected function initComponentProperties()
    {
        global $USER;

        $this->userId = $USER->GetId();
        if (!$this->userId) {
            throw new ComponentException('USER_NOT_AUTHORIZED');
        }

        $this->timeFrom = $_GET[$this->arParams['TIME_FROM_ALIAS']] ?? '';
        $this->timeTo = $_GET[$this->arParams['TIME_TO_ALIAS']] ?? '';
        if (!$this->timeFrom || !$this->timeTo) {
            throw new ComponentException('TIME_NOT_SET');
        } elseif (!self::validateDate($this->timeFrom) || !self::validateDate($this->timeTo)) {
            throw new ComponentException('INVALID_TIME');
        } elseif ($this->timeFrom > $this->timeTo) {
            throw new ComponentException('TIME_FROM_LARGER_THAN_TIME_TO');
        }
    }

    public static function validateDate($date, $format = 'd-m-Y H:i')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}