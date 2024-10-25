<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Выводит список новостей.
 *
 * Компонент выводит список анонсов новостей.
 *
 * Если указан ID или символьный код инфоблока, будут выведены новости этого инфоблока. В противном случае будут выведены новости всех инфоблоков указанного типа.
 *
 * Параметры компонента:
 *
 * + IBLOCK_TYPE &ndash; ID типа инфоблока. Если заданы IBLOCK_ID или IBLOCK_CODE, данный параметр игнорируется.
 * + IBLOCK_ID &ndash; ID инфоблока.
 * + IBLOCK_CODE &ndash; Символьный код инфоблока. Если задан IBLOCK_ID, данный параметр игнорируется.
 * + SECTION_ID &ndash; ID раздела, новости которого надо вывести. Если SECTION_ID и SECTION_CODE не заданы, выводятся новости корневого раздела инфоблока.
 * + SECTION_CODE &ndash; Символьный код раздела, новости которого надо вывести. Если задан SECTION_ID, данный параметр игнорируется. Если SECTION_ID и SECTION_CODE не заданы, выводятся новости корневого раздела инфоблока.
 * + INCLUDE_SUBSECTIONS &ndash; Если значение параметра равно "Y", то выводятся новости подразделов. Если значение равно "N", то новости подразделов не выводятся. По умолчанию "Y".
 * + DETAIL_URL &ndash; Шаблон адреса страницы детального просмотра новости. По умолчанию значение берётся из инфоблока.
 * + SECTION_URL &ndash; Шаблон адреса страницы раздела новостей. По умолчанию значение берётся из инфоблока.
 * + IBLOCK_URL &ndash; Шаблон адреса страницы инфоблока. По умолчанию значение берётся из инфоблока.
 * + FILTER &ndash; Ассоциативный массив, по которому будут отфильтрованы новости перед выводом на страницу. Если параметр не задан, фильтрация не производится.
 * + FILTER_CACHE &ndash; Если параметр равен "Y", то при использовании фильтра компонент будет кэшироваться. Если параметр равен "N", то при использовании фильтра компонент кэшироваться не будет. По умолчанию "N".
 * + CACHE_TYPE &ndash; Тип кэширования компонента. Допустимые значения: "A" &ndash; автоматическое кэширование, "Y" &ndash; управляемое кэширование, "N" &ndash; без кэширования. По умолчанию "A".
 * + CACHE_TIME &ndash; Время кэширования в секундах. По умолчанию 36000000.
 *
 * Для корректной работы компонента необходимо задать хотя бы один из параметров IBLOCK_TYPE, IBLOCK_ID, IBLOCK_CODE.
 */
class NewsListComponent extends \CBitrixComponent
{
    /**
     * @var array   $arIblocks  Массив ID инфоблоков, новости которых будут выведены.
     */
    protected $arIblocks = [];

    /**
     * @var array   $arSections Массив ID разделов, новости которых будут выведены. Если элемент массива равен false, будут выведены новости корневого раздела инфоблока. Если массив пустой, будут выведены новости всех разделов инфоблока.
     */
    protected $arSections = [];

    /**
     * Обрабатывает параметры инфоблока.
     *
     * @param array $arParams
     * @return array $arParams
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_TYPE'] = trim((string)($arParams['IBLOCK_TYPE'] ?? ''));
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['IBLOCK_CODE'] = trim((string)($arParams['IBLOCK_CODE'] ?? ''));
        $arParams['SECTION_ID'] = (int)($arParams['SECTION_ID'] ?? 0);
        $arParams['SECTION_CODE'] = trim((string)($arParams['SECTION_CODE'] ?? ''));
        $arParams['INCLUDE_SUBSECTIONS'] ??= 'Y';
        $arParams['INCLUDE_SUBSECTIONS'] = $arParams['INCLUDE_SUBSECTIONS'] !== 'N';
        $arParams['DETAIL_URL'] = trim((string)($arParams['DETAIL_URL'] ?? ''));
        $arParams['SECTION_URL'] = trim((string)($arParams['SECTION_URL'] ?? ''));
        $arParams['IBLOCK_URL'] = trim((string)($arParams['IBLOCK_URL'] ?? ''));
        $arParams['FILTER'] ??= array();
        if (!is_array($arParams['FILTER'])) {
            $arParams['FILTER'] = [];
        }
        $arParams['FILTER_CACHE'] ??= 'N';
        $arParams['FILTER_CACHE'] = $arParams['FILTER_CACHE'] === 'Y';
        $arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 36000000);
        if ($arParams['CACHE_TIME'] < 0) {
            $arParams['CACHE_TIME'] = 36000000;
        }

        return $arParams;
    }

    /**
     * Подключает языковые фразы.
     *
     * @return void
     */
    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    /**
     * Обрабатывает входные данные перед выводом на страницу сайта.
     *
     * Обрабатывает данные из массива $arParams и формирует массив $arResult, который передаётся в шаблон компонента.
     *
     * @return void
     */
    public function executeComponent()
    {
        if (!$this->arParams['FILTER_CACHE'] && $this->arParams['FILTER']) {
            $this->arParams['CACHE_TIME'] = 0;
        }

        if ($this->startResultCache()) {
            try {
                $this->checkModules('iblock');

                $this->initComponentProperties();

                $this->arResult = $this->getResultArray();

                $this->includeComponentTemplate();
            } catch (SystemException $e) {
                $this->abortResultCache();

                ShowError($e->getMessage());
            }
        }
    }

    /**
     * Подключает модули.
     *
     * @param string ...$modules
     * @return void
     * @throws Bitrix\Main\SystemException если не удалось подключить модуль.
     */
    protected function checkModules(...$modules)
    {
        foreach ($modules as $m) {
            if (!Loader::includeModule($m)) {
                throw new SystemException(Loc::getMessage('MODULE_NOT_FOUND'));
            }
        }
    }

    /**
     * Инициализирует свойства класса компонента.
     *
     * @return void
     */
    protected function initComponentProperties()
    {
        $this->initIblockArray();
        $this->initSectionArray();
    }

    /**
     * Инициализирует массив инфоблоков компонента.
     *
     * Заполняет массив $arIblocks ID инфоблоков, новости которых необходимо вывести.
     *
     * @return void
     * @throws Bitrix\Main\SystemException если ID или символьный код инфоблока, либо ID типа инфоблока некорректны, либо если не задан ни один из параметров IBLOCK_TYPE, IBLOCK_ID, IBLOCK_CODE.
     */
    protected function initIblockArray()
    {
        if (!$this->arParams['IBLOCK_ID'] && !$this->arParams['IBLOCK_CODE'] && !$this->arParams['IBLOCK_TYPE']) {
            throw new SystemException(Loc::getMessage('IBLOCK_FIELDS_EMPTY'));
        }

        if ($this->arParams['IBLOCK_ID'] > 0) {
            $iblock_id = $this->arParams['IBLOCK_ID'];
            if (!self::iblockExists($iblock_id)) {
                throw new SystemException(Loc::getMessage('IBLOCK_ID_NOT_VALID'));
            }
        } elseif ($this->arParams['IBLOCK_CODE']) {
            if (!$iblock_id = self::getIblockIdByCode($this->arParams['IBLOCK_CODE'])) {
                throw new SystemException(Loc::getMessage('IBLOCK_CODE_NOT_VALID'));
            }
        } else {
            $iblock_id = 0;
        }

        if ($iblock_id > 0) {
            $this->arIblocks = [$iblock_id];
        } elseif ($this->arParams['IBLOCK_TYPE']) {
            if (!self::iblockTypeExists($this->arParams['IBLOCK_TYPE'])) {
                throw new SystemException(Loc::getMessage('IBLOCK_TYPE_NOT_VALID'));
            }
            $this->arIblocks = self::getIblockByType($this->arParams['IBLOCK_TYPE']);
        }
    }

    /**
     * Инициализирует массив разделов инфоблока.
     *
     * Заполняет массив $arSections ID разделов инфоблока, новости которых необходимо вывести.
     *
     * @return void
     * @throws Bitrix\Main\SystemException если раздел с указанным ID или символьным кодом не найден в инфоблоках компонента.
     */
    protected function initSectionArray()
    {
        if ($this->arParams['SECTION_ID'] > 0) {
            $section_id = $this->arParams['SECTION_ID'];
            if (!self::sectionExists($section_id, $this->arIblocks)) {
                throw new SystemException(Loc::getMessage('SECTION_ID_NOT_VALID'));
            }
        } elseif ($this->arParams['SECTION_CODE']) {
            if (!$section_id = self::getSectionIdByCode($this->arParams['SECTION_CODE'])) {
                throw new SystemException(Loc::getMessage('SECTION_CODE_NOT_VALID'));
            }
        } else {
            $section_id = 0;
        }

        if ($section_id > 0) {
            $this->arSections = [$section_id];
            if ($this->arParams['INCLUDE_SUBSECTIONS']) {
                $this->arSections = array_merge($this->arSections, self::getSubsections($section_id));
            }
        } elseif ($this->arParams['INCLUDE_SUBSECTIONS']) {
            $this->arSections = array(); //if array is empty -> no section filter
        } else {
            $this->arSections = [false]; //root directory
        }
    }

    /**
     * Формирует массив $arResult.
     *
     * @return array
     */
    protected function getResultArray()
    {
        $arResult['ITEMS'] = $this->getResultItems();
        return $arResult;
    }

    /**
     * Формирует массив элементов инфоблока новостей.
     *
     * Формирует массив элементов инфоблока новостей. Элементы сгруппированы по ID инфоблоков. В качестве ключей для элементов используется ID элемента.
     *
     * @return array Если не удалось сформировать массив или по заданным условиям не найдено ни одного элемента, метод вернет пустой массив.
     */
    protected function getResultItems()
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }

        $elements_filter = ['IBLOCK_ID' => $this->arIblocks, 'ACTIVE' => 'Y'];
        if ($this->arSections) {
            $elements_filter['IBLOCK_SECTION_ID'] = $this->arSections;
        }
        $elements_filter += $this->arParams['FILTER'];

        $element_select = [
            '*',
            'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL',
            'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
            'LIST_PAGE_URL' => 'IBLOCK.LIST_PAGE_URL'
        ];

        $rsElements = ElementTable::getList([
            'filter' => $elements_filter,
            'select' => $element_select
        ]);

        $arElements = [];
        foreach ($this->arIblocks as $iblock) {
            $arElements[$iblock] = [];
        }
        while ($element = $rsElements->fetch()) {
            $picture_keys = ['PREVIEW_PICTURE', 'DETAIL_PICTURE'];
            self::convertPictureToArray($element, $picture_keys);

            $date_keys = ['TIMESTAMP_X', 'DATE_CREATE', 'ACTIVE_FROM', 'ACTIVE_TO'];
            self::convertDateToString($element, $date_keys);

            self::convertElementUrl(
                $element,
                $this->arParams['DETAIL_URL'],
                $this->arParams['SECTION_URL'],
                $this->arParams['IBLOCK_URL']);

            $arElements[$element['IBLOCK_ID']][$element['ID']] = $element;
        }
        return $arElements;
    }

    /**
     * Формирует массив с параметрами изображения по ID изображения.
     *
     * Находит в исходном массиве поля с ID изображений и заменяет в этих полях ID на массив с параметрами изображения. Если не удалось сформировать массив параметров, полю присваивается значение false. Преобразования осуществляются в массиве, который передан в качестве аргумента функции.
     *
     * @param array $array Массив, который надо преобразовать.
     * @param array $keys Ключи элементов массива $array, в которых хранятся ID изображения.
     * @return void
     */
    public static function convertPictureToArray(&$array, $keys)
    {
        if (is_array($array) && is_array($keys)) {
            foreach ($keys as $k) {
                if (isset($array[$k])) {
                    $array[$k] = \CFile::GetFileArray($array[$k]);
                }
            }
        }
    }

    /**
     * Преобразовывает дату в строку.
     *
     * Находит в исходном массиве поля с сущностями объекта Bitrix\Main\Type\DateTime и преобразует их в строку. Если не удалось преобразовать дату к строке, полю присвается пустая строка. Преобразования осуществляются в массиве, который передан в качестве аргумента функции.
     *
     * @param array $array Массив, который надо преобразовать.
     * @param array $keys Ключи элементов массива $array, в которых хранятся даты.
     * @return void
     */
    public static function convertDateToString(&$array, $keys)
    {
        if (is_array($array) && is_array($keys)) {
            foreach ($keys as $k) {
                if (isset($array[$k])) {
                    $array[$k] = $array[$k] instanceof DateTime ? $array[$k]->toString() : '';
                }
            }
        }
    }

    /**
     * Формирует URL-адрес из шаблона URL-адреса.
     *
     * Преобразовывает шаблоны URL-адреса страницы детального просмотра, URL-адреса страницы раздела и URL-адреса страницы инфоблока. Адреса должны хранится в полях массива с ключами "DETAIL_PAGE_URL", "SECTION_PAGE_URL" и "LIST_PAGE_URL" массива полей элемента инфоблока.
     *
     * Если аргументы с шаблонами соответствующих URL-адресов не переданы, будут использованы шаблоны из массива полей элемента.
     *
     * В массиве полей элемента должны присутствовать поля с ключами, которые используются в шаблонах.
     *
     * Если не удалось преобразовать шаблон URL-адреса, полю присвается пустая строка. Преобразования осуществляются в массиве, который передан в качестве аргумента функции.
     *
     * @param array $element_array Массив полей элемента.
     * @param string $detail_url Шаблон адреса страницы детального просмотра. Необязательный параметр.
     * @param string $section_url Шаблон адреса страницы раздела. Необязательный параметр.
     * @param string $iblock_url Шаблон адреса страницы инфоблока. Необязательный параметр.
     * @return void
     */
    public static function convertElementUrl(&$element_array, $detail_url = '', $section_url = '', $iblock_url = '')
    {
        if (!Loader::includeModule('iblock')) {
            return;
        }

        if (isset($element_array['DETAIL_PAGE_URL'])) {
            if ($detail_url) {
                $element_array['DETAIL_PAGE_URL'] = $detail_url;
            }
            $element_array['DETAIL_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
                $element_array['DETAIL_PAGE_URL'],
                $element_array,
                false,
                'E'
            );
        }

        if (isset($element_array['SECTION_PAGE_URL'])) {
            if ($section_url) {
                $element_array['SECTION_PAGE_URL'] = $section_url;
            }
            $element_array['SECTION_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
                $element_array['SECTION_PAGE_URL'],
                $element_array,
                false,
                'E'
            );
        }

        if (isset($element_array['LIST_PAGE_URL'])) {
            if ($iblock_url) {
                $element_array['LIST_PAGE_URL'] = $iblock_url;
            }
            $element_array['LIST_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
                $element_array['LIST_PAGE_URL'],
                $element_array,
                false,
                'E'
            );
        }
    }

    /**
     * Формирует массив с ID инфоблоков заданного типа.
     *
     * @param string $iblock_type
     * @return array Массив с ID инфоблоков. Если не удалось сформировать массив или не найдено ни одного инфоблока указанного типа, функция вернет пустой массив.
     */
    public static function getIblockByType($iblock_type)
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        $rsIblocks = IblockTable::getList([
            'filter' => ['IBLOCK_TYPE_ID' => $iblock_type, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        $arIblocks = [];
        while ($iblock = $rsIblocks->fetch()) {
            $arIblocks[] = $iblock['ID'];
        }
        return $arIblocks;
    }

    /**
     * Формирует массив с ID подразделов заданного раздела инфоблока.
     *
     * @param int $section_id
     * @return array Массив с ID подразделов. Если не удалось сформировать массив или не найдено ни одного подраздела, функция вернет пустой массив.
     */
    public static function getSubsections($section_id)
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        $rsSections = SectionTable::getList([
            'filter' => ['IBLOCK_SECTION_ID' => $section_id, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        $arSections = [];
        while ($section = $rsSections->fetch()) {
            $arSections[] = $section['ID'];
            $arSections = array_merge($arSections, self::getSubsections($section['ID']));
        }
        return $arSections;
    }

    /**
     * Проверяет существование типа инфоблока с заданным ID.
     *
     * @param string $iblock_type
     * @return bool
     */
    public static function iblockTypeExists($iblock_type)
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        return !is_null(TypeTable::getRow([
            'filter' => ['ID' => $iblock_type],
            'select' => ['ID']
        ]));
    }

    /**
     * Проверяет существование инфоблока с заданным ID.
     *
     * @param int $iblock_id
     * @return bool
     */
    public static function iblockExists($iblock_id)
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        return !is_null(IblockTable::getRow([
            'filter' => ['ID' => $iblock_id, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]));
    }

    /**
     * Проверяет существование раздела инфоблока с заданным ID.
     *
     * Проверяет существование раздела инфоблока с заданным ID. Если передан аргумент с ID инфоблока(ов), поиск производится только среди разделов этого инфоблока(ов). В противном случае поиск осуществляется среди разделов всех инфоблоков.
     *
     * @param int $section_id
     * @param int|array $iblocks ID инфоблоков. Необязательный параметр.
     * @return bool
     */
    public static function sectionExists($section_id, $iblocks = '')
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        $filter = ['ID' => $section_id, 'ACTIVE' => 'Y'];
        if ($iblocks) {
            $filter['IBLOCK_ID'] = $iblocks;
        }
        return !is_null(SectionTable::getRow([
            'filter' => $filter,
            'select' => ['ID']
        ]));
    }

    /**
     * Находит ID инфоблока по заданному символьному коду.
     *
     * @param $iblock_code
     * @return int|false Возвращает ID инфоблока. Если найти инфоблок не удалось, возвращает false.
     */
    public static function getIblockIdByCode($iblock_code)
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        $iblock_item = IblockTable::getRow([
            'filter' => ['CODE' => $iblock_code, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        if (is_null($iblock_item)) {
            return false;
        } else {
            return $iblock_item['ID'];
        }
    }

    /**
     * Находит ID раздела инфоблока по заданному символьному коду.
     *
     * @param $section_code
     * @return int|false Возвращает ID раздела инфоблока. Если найти инфоблок не удалось, возвращает false.
     */
    public static function getSectionIdByCode($section_code)
    {
        if (!Loader::includeModule('iblock')) {
            return false;
        }
        $section_item = SectionTable::getRow([
            'filter' => ['CODE' => $section_code, 'ACTIVE' => 'Y'],
            'select' => ['ID']
        ]);
        if (is_null($section_item)) {
            return false;
        } else {
            return $section_item['ID'];
        }
    }
}
