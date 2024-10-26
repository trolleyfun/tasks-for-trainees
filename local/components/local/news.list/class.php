<?php

/**
 * @package Components
 */

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Dev\Site\Exceptions\ComponentException;
use Dev\Site\Iblock\IblockQueries;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Выводит список новостей.
 *
 * Компонент выводит список анонсов новостей.
 *
 * Если указан ID или символьный код инфоблока, будут выведены новости этого инфоблока. В противном
 * случае будут выведены новости всех инфоблоков указанного типа.
 *
 * Параметры компонента:
 *
 * + IBLOCK_TYPE &ndash; ID типа инфоблока. Если заданы IBLOCK_ID или IBLOCK_CODE, данный параметр игнорируется.
 * + IBLOCK_ID &ndash; ID инфоблока.
 * + IBLOCK_CODE &ndash; Символьный код инфоблока. Если задан IBLOCK_ID, данный параметр игнорируется.
 * + SECTION_ID &ndash; ID раздела, новости которого надо вывести. Если SECTION_ID и SECTION_CODE не заданы,
 * выводятся новости корневого раздела инфоблока.
 * + SECTION_CODE &ndash; Символьный код раздела, новости которого надо вывести. Если задан SECTION_ID,
 * данный параметр игнорируется. Если SECTION_ID и SECTION_CODE не заданы, выводятся новости
 * корневого раздела инфоблока.
 * + INCLUDE_SUBSECTIONS &ndash; Если значение параметра равно "Y", то выводятся новости подразделов.
 * Если значение равно "N", то новости подразделов не выводятся. По умолчанию "Y".
 * + DETAIL_URL &ndash; Шаблон адреса страницы детального просмотра новости. По умолчанию значение
 * берётся из инфоблока.
 * + SECTION_URL &ndash; Шаблон адреса страницы раздела новостей. По умолчанию значение берётся из инфоблока.
 * + IBLOCK_URL &ndash; Шаблон адреса страницы инфоблока. По умолчанию значение берётся из инфоблока.
 * + FILTER &ndash; Ассоциативный массив, по которому будут отфильтрованы новости перед выводом на страницу.
 * Если параметр не задан, фильтрация не производится.
 * + FILTER_CACHE &ndash; Если параметр равен "Y", то при использовании фильтра компонент будет кэшироваться.
 * Если параметр равен "N", то при использовании фильтра компонент кэшироваться не будет. По умолчанию "N".
 * + CACHE_TYPE &ndash; Тип кэширования компонента. Допустимые значения: "A" &ndash; автоматическое кэширование,
 * "Y" &ndash; управляемое кэширование, "N" &ndash; без кэширования. По умолчанию "A".
 * + CACHE_TIME &ndash; Время кэширования в секундах. По умолчанию 36000000.
 *
 * Для корректной работы компонента необходимо задать хотя бы один из параметров IBLOCK_TYPE, IBLOCK_ID, IBLOCK_CODE.
 * Остальные параметры необязательные.
 */
class NewsListComponent extends \CBitrixComponent
{
    /**
     * @var int[] $arIblocks Массив ID инфоблоков, новости которых будут выведены.
     */
    protected $arIblocks = [];

    /**
     * @var (int|false)[] $arSections   Массив ID разделов, новости которых будут выведены. Если
     *                                  элемент массива равен false, будут выведены новости корневого
     *                                  раздела инфоблока. Если массив пустой, будут выведены новости
     *                                  всеx разделов инфоблока.
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
     * @return void
     */
    public function executeComponent()
    {
        if (!$this->arParams['FILTER_CACHE'] && $this->arParams['FILTER']) {
            $this->arParams['CACHE_TIME'] = 0;
        }

        if ($this->startResultCache()) {
            try {
                $this->checkModules('iblock', 'dev.site');

                $this->initComponentProperties();

                $this->arResult = $this->getResultArray();

                $this->includeComponentTemplate();
            } catch (ComponentException $e) {
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
     * Заполняет массив $arIblocks значениями ID инфоблоков, новости которых необходимо вывести.
     *
     * @return void
     * @throws Dev\Site\Exceptions\ComponentException
     *      + некорректный ID инфоблока
     *      + некорректный символьный код инфоблока
     *      + некорректный ID типа инфоблока
     *      + не задан ни один из параметров IBLOCK_TYPE, IBLOCK_ID, IBLOCK_CODE.
     */
    protected function initIblockArray()
    {
        if (!$this->arParams['IBLOCK_ID'] && !$this->arParams['IBLOCK_CODE'] && !$this->arParams['IBLOCK_TYPE']) {
            throw new ComponentException(Loc::getMessage('IBLOCK_FIELDS_EMPTY'));
        }

        if ($this->arParams['IBLOCK_ID'] > 0) {
            $iblock_id = $this->arParams['IBLOCK_ID'];
            if (!IblockQueries::iblockExists($iblock_id)) {
                throw new ComponentException(Loc::getMessage('IBLOCK_ID_NOT_VALID'));
            }
        } elseif ($this->arParams['IBLOCK_CODE']) {
            if (!$iblock_id = IblockQueries::getIblockIdByCode($this->arParams['IBLOCK_CODE'])) {
                throw new ComponentException(Loc::getMessage('IBLOCK_CODE_NOT_VALID'));
            }
        } else {
            $iblock_id = 0;
        }

        if ($iblock_id > 0) {
            $this->arIblocks = [$iblock_id];
        } elseif ($this->arParams['IBLOCK_TYPE']) {
            if (!IblockQueries::iblockTypeExists($this->arParams['IBLOCK_TYPE'])) {
                throw new ComponentException(Loc::getMessage('IBLOCK_TYPE_NOT_VALID'));
            }
            $this->arIblocks = IblockQueries::getIblockByType($this->arParams['IBLOCK_TYPE']);
        }
    }

    /**
     * Инициализирует массив разделов инфоблока.
     *
     * Заполняет массив $arSections значениями ID разделов инфоблока, новости которых необходимо вывести.
     *
     * @return void
     * @throws Dev\Site\Exceptions\ComponentException
     *      + раздел с указанным ID не найден в инфоблоках, перечисленных в массиве $arIblocks
     *      + раздел с указанным символьным кодом не найден в инфоблоках, перечисленных в массиве $arIblocks
     */
    protected function initSectionArray()
    {
        if ($this->arParams['SECTION_ID'] > 0) {
            $section_id = $this->arParams['SECTION_ID'];
            if (!IblockQueries::sectionExists($section_id, $this->arIblocks)) {
                throw new ComponentException(Loc::getMessage('SECTION_ID_NOT_VALID'));
            }
        } elseif ($this->arParams['SECTION_CODE']) {
            if (!$section_id = IblockQueries::getSectionIdByCode($this->arParams['SECTION_CODE'])) {
                throw new ComponentException(Loc::getMessage('SECTION_CODE_NOT_VALID'));
            }
        } else {
            $section_id = 0;
        }

        if ($section_id > 0) {
            $this->arSections = [$section_id];
            if ($this->arParams['INCLUDE_SUBSECTIONS']) {
                $this->arSections = array_merge($this->arSections, IblockQueries::getSubsections($section_id));
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
     * Формирует массив $arResult. Структура массива:
     * ```
     * Array
     * (
     *     ['ITEMS'] =>
     * )
     * ```
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
     * Формирует массив элементов инфоблока новостей. Элементы сгруппированы по ID инфоблоков.
     *
     * Структура массива:
     * ```
     * Array
     * (
     *      [IBLOCK_ID] => Array
     *          (
     *              [ELEMENT_ID] => Array()
     *          )
     * )
     * ```
     *
     * @return array Если не удалось сформировать массив или по заданным условиям не найдено
     *               ни одного элемента, метод вернет пустой массив.
     */
    protected function getResultItems()
    {
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
            IblockQueries::convertPictureToArray($element, $picture_keys);

            $date_keys = ['TIMESTAMP_X', 'DATE_CREATE', 'ACTIVE_FROM', 'ACTIVE_TO'];
            IblockQueries::convertDateToString($element, $date_keys);

            IblockQueries::convertElementUrl(
                $element,
                $this->arParams['DETAIL_URL'],
                $this->arParams['SECTION_URL'],
                $this->arParams['IBLOCK_URL']);

            $arElements[$element['IBLOCK_ID']][$element['ID']] = $element;
        }
        return $arElements;
    }
}
