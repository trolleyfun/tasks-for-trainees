<?php

/**
 * @package Components
 */

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Dev\Site\Exceptions\ComponentException;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Компонент выводит список доступных для поездки служебных автомобилей.
 *
 * Компонент по GET-запросу, содержащему время начала и окончания поездки, формирует список доступных
 * для поездки служебных автомобилей. Учитывается, какие классы автомобилей доступны текущему пользователю.
 */
class CarsAvailableComponent extends CBitrixComponent
{
    protected $userId;

    protected $timeFrom;

    protected $timeTo;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['CARS_IBLOCK_CODE'] = trim(((string)$arParams['CARS_IBLOCK_CODE'] ?? 'cars'));
        $arParams['ORDERS_IBLOCK_CODE'] = trim(((string)$arParams['ORDERS_IBLOCK_CODE'] ?? 'orders'));
        $arParams['JOBS_IBLOCK_CODE'] = trim(((string)$arParams['JOBS_IBLOCK_CODE'] ?? 'jobs'));
        $arParams['DRIVERS_IBLOCK_CODE'] = trim(((string)$arParams['DRIVERS_IBLOCK_CODE'] ?? 'drivers'));
        $arParams['TIME_FROM_ALIAS'] = trim(((string)$arParams['TIME_FROM_ALIAS'] ?? 'from'));
        $arParams['TIME_TO_ALIAS'] = trim(((string)$arParams['TIME_TO_ALIAS'] ?? 'to'));

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
        } catch (SystemException $e) {
            ShowError(Loc::getMessage('OTHER_ERRORS'));
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

        $this->userId = $USER->GetID();
        if (!$this->userId) {
            throw new ComponentException(Loc::getMessage('USER_NOT_AUTHORIZED'));
        }

        $this->timeFrom = urldecode($_GET[$this->arParams['TIME_FROM_ALIAS']] ?? '');
        $this->timeTo = urldecode($_GET[$this->arParams['TIME_TO_ALIAS']] ?? '');
        if (!$this->timeFrom || !$this->timeTo) {
            throw new ComponentException(Loc::getMessage('TIME_NOT_SET'));
        } elseif (!self::validateDate($this->timeFrom) || !self::validateDate($this->timeTo)) {
            throw new ComponentException(Loc::getMessage('INVALID_TIME'));
        } elseif ($this->timeFrom > $this->timeTo) {
            throw new ComponentException(Loc::getMessage('TIME_FROM_LARGER_THAN_TIME_TO'));
        }

        if (
            !self::iblockCodeExists($this->arParams['CARS_IBLOCK_CODE'])
            || !self::iblockCodeExists($this->arParams['JOBS_IBLOCK_CODE'])
            || !self::iblockCodeExists($this->arParams['ORDERS_IBLOCK_CODE'])
            || !self::iblockCodeExists($this->arParams['DRIVERS_IBLOCK_CODE'])
        ) {
            throw new ComponentException(Loc::getMessage('IBLOCK_CODE_INVALID'));
        }
    }

    protected function getResultArray()
    {
        $result = ['TIME_FROM' => $this->timeFrom, 'TIME_TO' => $this->timeTo];

        $carClasses = $this->getUserCarClasses();
        if (!$carClasses) {
            throw new ComponentException(Loc::getMessage('NO_CAR_CLASSES_FOUND'));
        }
        $result['CLASSES'] = $carClasses;

        $result['CARS'] = $this->getCarsAvailable($carClasses);

        return $result;
    }

    protected function getCarsAvailable($availableClasses)
    {
        $carOrderedIds = $this->getCarsOrdered();

        $rsCars = CIBlockElement::GetList(
            array(),
            [
                'IBLOCK_CODE' => $this->arParams['CARS_IBLOCK_CODE'],
                '!ID' => $carOrderedIds,
                'PROPERTY_CLASS' => $availableClasses
            ],
            false,
            false,
            ['ID', 'NAME', 'PROPERTY_REG_ID', 'PROPERTY_DRIVER', 'PROPERTY_CLASS']
        );

        $cars = [];
        while ($row = $rsCars->Fetch()) {
            $cars[$row['ID']]['ID'] = $row['ID'];
            $cars[$row['ID']]['MODEL'] = $row['NAME'];
            $cars[$row['ID']]['REG_ID'] = $row['PROPERTY_REG_ID_VALUE'];
            $cars[$row['ID']]['CLASS'] = $row['PROPERTY_CLASS_VALUE'];
            $cars[$row['ID']]['DRIVER'] = $this->getDriverById($row['PROPERTY_DRIVER_VALUE']);
        }

        return $cars;
    }

    protected function getUserCarClasses()
    {
        $arUser = UserTable::getRow([
            'filter' => ['ID' => $this->userId],
            'select' => ['ID', 'UF_POSITION']
        ]);
        if (!is_null($arUser) && !empty($arUser['UF_POSITION'])) {
            $userPositionId = $arUser['UF_POSITION'];
        } else {
            $userPositionId = false;
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
            if ($row['PROPERTY_CAR_CLASS_VALUE']) {
                $userCarClasses[] = $row['PROPERTY_CAR_CLASS_VALUE'];
            }
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

        return array_unique($carIds);
    }

    protected function getDriverById($driverId)
    {
        if (!$driverId) {
            $driverId = false;
        }
        $arDriver = CIBlockElement::GetList(
            array(),
            ['ID' => $driverId],
            false,
            false,
            ['ID', 'PROPERTY_FIRST_NAME', 'PROPERTY_SECOND_NAME', 'PROPERTY_AGE']
        )->Fetch();

        $result = [];
        if ($arDriver) {
            $result['ID'] = $arDriver['ID'];
            $result['FIRST_NAME'] = $arDriver['PROPERTY_FIRST_NAME_VALUE'];
            $result['SECOND_NAME'] = $arDriver['PROPERTY_SECOND_NAME_VALUE'];
            $result['AGE'] = $arDriver['PROPERTY_AGE_VALUE'];
        }

        return $result;
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
            'filter' => ['CODE' => $iblock_code],
            'select' => ['ID']
        ]));
    }
}
