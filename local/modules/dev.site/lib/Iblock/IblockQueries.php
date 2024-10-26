<?php

namespace Dev\Site\Iblock;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

/**
 * Класс для работы с выборками инфоблоков.
 */
class IblockQueries
{
    /**
     * Преобразует ID изображения в массив с параметрами изображения.
     *
     * Находит в исходном массиве поля, соответствующие ключам, которые были переданы в качестве
     * аргумента функции, и формирует для этих полей массив параметров изображения.
     *
     * Если не удалось сформировать массив параметров, полю присваивается значение false.
     *
     * Преобразования осуществляются в массиве, который передан в качестве аргумента функции.
     *
     * @param &array $array Массив, который необходимо преобразовать.
     * @param (string|int)[] $keys Ключи элементов массива $array, в которых хранятся ID изображений.
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
     * Преобразовывает сущность объекта Bitrix\Main\Type\DateTime в строку.
     *
     * Находит в исходном массиве поля, соответствующие ключам, которые были переданы в качестве
     * аргумента функции, и формирует для этих полей строку, содержащую дату.
     *
     * Если не удалось преобразовать дату к строке, полю присвается пустая строка.
     *
     * Преобразования осуществляются в массиве, который передан в качестве аргумента функции.
     *
     * @param array &$array Массив, который необходимо преобразовать.
     * @param (string|int)[] $keys Ключи элементов массива $array, в которых хранятся даты.
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
     * Формирует URL-адрес по шаблону URL-адреса.
     *
     * Преобразовывает шаблоны URL-адреса страницы детального просмотра, URL-адреса страницы раздела
     * и URL-адреса страницы инфоблока.
     *
     * Адреса хранятся в полях массива с ключами "DETAIL_PAGE_URL", "SECTION_PAGE_URL" и "LIST_PAGE_URL" соотвественно.
     * В исходном массиве должны присутствовать эти поля. Также в исходном массиве должны присутствовать поля,
     * которые используются в шаблонах адресов (ID, SECTION_ID, IBLOCK_ID и др.).
     *
     * Если в функцию переданы аргументы с шаблонами адресов, будут использованы эти шаблоны. В противном случае
     * будут использованы шаблоны, заданные в параметрах инфоблока.
     *
     * Если не удалось преобразовать шаблон URL-адреса, полю присвается пустая строка. Преобразования
     * осуществляются в массиве, который передан в качестве аргумента функции.
     *
     * @param array &$element_array Массив полей элемента.
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
     * @return int[] Массив с ID инфоблоков. Если не удалось сформировать массив или не найдено
     *               ни одного инфоблока указанного типа, функция вернет пустой массив.
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
     * Формирует массив с ID подразделов указанного раздела инфоблока.
     *
     * @param int $section_id
     * @return int[] Массив с ID подразделов. Если не удалось сформировать массив или не найдено
     *               ни одного подраздела, функция вернет пустой массив.
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
     * Проверяет существование раздела инфоблока с заданным ID.
     *
     * Если передан аргумент с ID инфоблока(ов), поиск производится только среди разделов этого инфоблока(ов).
     * В противном случае поиск осуществляется среди разделов всех инфоблоков.
     *
     * @param int $section_id
     * @param int|int[] $iblocks ID инфоблоков. Необязательный параметр.
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
     * @param string $iblock_code
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
     * @param string $section_code
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
