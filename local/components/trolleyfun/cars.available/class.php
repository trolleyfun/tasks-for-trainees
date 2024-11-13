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
 * Компонент по GET-запросу, содержащему дату и время начала и окончания поездки, формирует список доступных
 * для поездки служебных автомобилей. Учитывается, какие классы автомобилей доступны текущему пользователю.
 *
 * Дата и время должны передаваться в формате "ГГГГ-ММ-ДД ЧЧ:ММ" (24-часовой формат).
 */
class CarsAvailableComponent extends CBitrixComponent
{
    /**
     * @var int $userId ID текущего пользователя
     */
    protected $userId;

    /**
     * @var string $timeFrom Время начала поездки в формате "ГГГГ-ММ-ДД ЧЧ:ММ" (24-часовой формат)
     */
    protected $timeFrom;

    /**
     * @var string $timeTo Время окончания поездки в формате "ГГГГ-ММ-ДД ЧЧ:ММ" (24-часовой формат)
     */
    protected $timeTo;

    /**
     * Обрабатывает параметры компонента.
     *
     * Параметры компонента:
     * + `CARS_IBLOCK_CODE` &ndash; Символьный код инфоблока, в котором хранится информация об автомобилях.
     * Необязательный параметр. Значение по умолчанию "cars".
     * + `ORDERS_IBLOCK_CODE` &ndash; Символьный код инфоблока, в котором хранится информация о поездках.
     * Необязательный параметр. Значение по умолчанию "orders".
     * + `JOBS_IBLOCK_CODE` &ndash; Символьный код инфоблока, в котором хранится информация о должностях сотрудников.
     * Необязательный параметр. Значение по умолчанию "jobs".
     * + `DRIVERS_IBLOCK_CODE` &ndash; Символьный код инфоблока, в котором хранится информация о водителях.
     * Необязательный параметр. Значение по умолчанию "drivers".
     * + `TIME_FROM_ALIAS` &ndash; Название GET-параметра, в котором передается дата и время начала поездки.
     * + `TIME_TO_ALIAS` &ndash; Название GET-параметра, в котором передается дата и время окончания поездки.
     *
     * @param array $arParams Параметры компонента
     * @return array Обработанные параметры компонента
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['CARS_IBLOCK_CODE'] = trim((string)($arParams['CARS_IBLOCK_CODE'] ?? 'cars'));
        $arParams['ORDERS_IBLOCK_CODE'] = trim((string)($arParams['ORDERS_IBLOCK_CODE'] ?? 'orders'));
        $arParams['JOBS_IBLOCK_CODE'] = trim((string)($arParams['JOBS_IBLOCK_CODE'] ?? 'jobs'));
        $arParams['DRIVERS_IBLOCK_CODE'] = trim((string)($arParams['DRIVERS_IBLOCK_CODE'] ?? 'drivers'));
        $arParams['TIME_FROM_ALIAS'] = trim((string)($arParams['TIME_FROM_ALIAS'] ?? 'from'));
        $arParams['TIME_TO_ALIAS'] = trim((string)($arParams['TIME_TO_ALIAS'] ?? 'to'));

        return $arParams;
    }

    /**
     * Подключает языковые файлы.
     *
     * @return void
     */
    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    /**
     * Формирует данные, которые необходимо вывести на страницу сайта.
     *
     * Обрабатывает данные из массива $arParams и формирует массив $arResult, который передаётся в шаблон компонента.
     *
     * Структура массива $arResult:
     * ```
     * Array
     * (
     *      [TIME_FROM] =>
     *      [TIME_TO] =>
     *      [CLASSES] => Array
     *                   (
     *                          [порядковый_номер] =>
     *                   )
     *      [CARS] => Array
     *                (
     *                      [ID_автомобиля] => Array
     *                                (
     *                                      [ID] =>
     *                                      [MODEL] =>
     *                                      [REG_ID] =>
     *                                      [CLASS] =>
     *                                      [DRIVER] => Array
     *                                                  (
     *                                                      [ID] =>
     *                                                      [FIRST_NAME] =>
     *                                                      [LAST_NAME] =>
     *                                                      [AGE] =>
     *                                                  )
     *                                )
     *                )
     * )
     * ```
     *
     * + `TIME_FROM` &ndash; Время начала поездки в формате "ГГГГ-ММ-ДД ЧЧ:ММ" (24-часовой формат).
     * + `TIME_TO` &ndash; Время окончания поездки в формате "ГГГГ-ММ-ДД ЧЧ:ММ" (24-часовой формат).
     * + `CLASSES` &ndash; Доступные для текущего пользователя классы автомобилей.
     * + `CARS` &ndash; Информация о доступных для поездки автомобилях и водителях этих автомобилей.
     *
     * @return void
     */
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

    /**
     * Подключает модули.
     *
     * @param string ...$modules
     * @return void
     * @throws Dev\Site\Exceptions\ComponentException если не удалось подключить модуль.
     */
    protected function checkModules(...$modules)
    {
        foreach ($modules as $m) {
            if (!Loader::includeModule($m)) {
                throw new ComponentException(Loc::getMessage('MODULE_NOT_FOUND'));
            }
        }
    }

    /**
     * Инициализирует свойства класса компонента и проверяет корректность входных данных.
     *
     * @return void
     */
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
        } elseif (
            !self::validateDate($this->timeFrom, 'Y-m-d H:i')
            || !self::validateDate($this->timeTo, 'Y-m-d H:i')
        ) {
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

     /**
     * Формирует массив $arResult.
     *
     * @return array
     */
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

    /**
     * Формирует массив с доступными для поездки автомобилями.
     *
     * Если нет ни одного доступного для поездки автомобиля, возвращает пустой массив.
     *
     * @param string|string[] $availableClasses Доступные для пользователя классы автомобилей
     * @return array
     */
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

    /**
     * Возвращает массив с доступными для текущего пользователя классами автомобилей.
     *
     * Если у пользователя не указана должность или не задан ни один доступный класс автомобилей,
     * возвращает пустой массив.
     *
     * @return array
     */
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

    /**
     * Формирует массив, содержащий ID автомобилей, которые заняты в указанное для поездки время.
     *
     * Если все автомобили свободны, возвращает пустой массив.
     *
     * @return array
     */
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

    /**
     * Формирует массив с информацией о водителе автомобиля.
     *
     * Если водитель с указанным ID не найден, возвращает пустую строку.
     *
     * @param int $driverId ID водителя автомобиля
     * @return array
     */
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
            ['ID', 'PROPERTY_FIRST_NAME', 'PROPERTY_LAST_NAME', 'PROPERTY_AGE']
        )->Fetch();

        if (!$arDriver) {
            $result = '';
        } else {
            $result = [
                'ID' => $arDriver['ID'],
                'FIRST_NAME' => $arDriver['PROPERTY_FIRST_NAME_VALUE'],
                'LAST_NAME' => $arDriver['PROPERTY_LAST_NAME_VALUE'],
                'AGE' => $arDriver['PROPERTY_AGE_VALUE']
            ];
        }

        return $result;
    }

    /**
     * Проверяет корректность даты в формате строки.
     *
     * Возвращает true, если дата корректна. Возвращает false, если дата не корректна.
     *
     * @param string $date Дата
     * @param string $format Формат даты. Необязательный параметр. По умолчанию "Y-m-d H:i".
     * @return bool
     */
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
