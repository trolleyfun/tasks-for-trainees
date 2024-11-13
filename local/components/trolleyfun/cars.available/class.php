<?php

/**
 * @package Components
 */

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Dev\Site\Exceptions\ComponentException;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class CarsAvailableComponent extends \CBitrixComponent
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

            $this->arResult = $this->getResultArray();

            $this->includeComponentTemplate();
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

        if (
            !self::iblockCodeExists($this->arParams['CARS_IBLOCK_CODE'])
            || !self::iblockCodeExists($this->arParams['JOBS_IBLOCK_CODE'])
            || !self::iblockCodeExists($this->arParams['ORDERS_IBLOCK_CODE'])
        ) {
            throw new ComponentException('IBLOCK_CODE_INVALID');
        }
    }

    protected function getResultArray()
    {
        $result = ['TIME_FROM' => $this->timeFrom, 'TIME_TO' => $this->timeTo];

        $result['CARS'] = $this->getCarsAvailable();

        return $result;
    }

    protected function getCarsAvailable()
    {
        $carClasses = $this->getUserCarClasses();
        if (!$carClasses) {
            throw new ComponentException('NO_CAR_CLASSES_FOUND');
        }

        $carOrderedIds = $this->getCarsOrdered();
    }

    protected function getUserCarClasses()
    {
        $arUser = UserTable::getRow([
            'filter' => ['ID' => $this->userId],
            'select' => ['ID', 'UF_POSITION']
        ]);
        if (!is_null($arUser)) {
            $userPositionId = $arUser['UF_POSITION'];
        } else {
            $userPositionId = '';
        }

        $rsUserCarClasses = CIBlockElement::GetList(
            array(),
            ['IBLOCK_CODE' => $this->arParams['JOBS_IBLOCK_CODE'], 'ID' => $userPositionId],
            false,
            false,
            ['ID', 'PROPERTY_CAR_CLASS']
        );
        $userCarClasses = [];
        while ($row = $rsUserCarClasses->Fetch()) {
            $userCarClasses[] = $row['PROPERTY_CAR_CLASS_VALUE'];
        }

        return $userCarClasses;
    }

    protected function getCarsOrdered()
    {
        $rsCars = CIBlockElement::GetList(
            array(),
            [
                'IBLOCK_CODE' => $this->arParams['ORDERS_IBLOCK_CODE'],
                '<PROPERTY_TIME_FROM' => $this->timeTo,
                '>PROPERTY_TIME_TO' => $this->timeFrom
            ],
            false,
            false,
            ['ID', 'PROPERTY_CAR']
        );

        $carIds = [];
        while ($row = $rsCars->Fetch()) {
            $carIds[] = $row['PROPERTY_CAR_VALUE'];
        }

        return $carIds;
    }

    public static function validateDate($date, $format = 'Y-m-d H:i')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

     /**
     * Проверяет существование инфоблока с заданным символьным кодом.
     *
     * @param string $iblock_code
     * @return bool
     */
    public static function iblockCodeExists($iblock_code)
    {
        return !is_null(IblockTable::getRow([
            'filter' => ['CODE' => $iblock_code, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]));
    }
}