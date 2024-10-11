<?php

namespace Local;

use CIBlock;
use CIBlockProperty;

\Bitrix\Main\Loader::includeModule('iblock');

class IBlockElementLoader
{
    public $arrProperties = [];
    public $arrHeaders = [];
    public $elementName = '';

    public function initProperties($iblock_id)
    {
        $rsProperties = CIBlock::getProperties($iblock_id, array(), array());
        while ($property = $rsProperties->GetNext()) {
            $key = trim(strtolower($property['CODE']));
            $this->arrProperties[$key]['code'] = $property['CODE'];
            $this->arrProperties[$key]['type'] = $property['PROPERTY_TYPE'];
            $this->arrProperties[$key]['multiple'] = $property['MULTIPLE'];
            $this->arrProperties[$key]['value'] = '';
        }
    }

    public function initHeaders($headers)
    {
        foreach ($headers as $value) {
            $this->arrHeaders[] = trim(strtolower($value));
        }
    }

    public function clearElementValues()
    {
        foreach ($this->arrProperties as $property) {
            $property['value'] = '';
        }
        $this->elementName = '';
    }

    public function setElementValues($elementValues, $valuesDivider)
    {
        foreach ($elementValues as $i=>$value) {
            if (isset($this->arrHeaders[$i])) {
                $key = $this->arrHeaders[$i];
                $value = str_replace(["\r\n", "\n", "\r"], ' ', $value);
                if ($key == 'name') {
                    $this->elementName = trim($value);
                } elseif (isset($this->arrProperties[$key])) {
                    if ($this->arrProperties[$key]['multiple'] == 'Y') {
                        $value = self::divideMultipleProperties($value, $valuesDivider);
                        if ($this->arrProperties[$key]['type'] == 'L') {
                            foreach ($value as $i=>&$item) {
                                $id = self::getListValueId($this->arrProperties[$key]['code'], $item);
                                if ($id) {
                                    $item = $id;
                                } else {
                                    $item = '';
                                }
                            }
                        }
                        unset($item);
                    } elseif ($this->arrProperties[$key]['type'] == 'L') {
                        $id = self::getListValueId($this->arrProperties[$key]['code'], $value);
                        if ($id) {
                            $value = $id;
                        } else {
                            $value = '';
                        }
                    } else {
                        $value = trim($value);
                    }
                    $this->arrProperties[$key]['value'] = $value;
                }
            }
        }
    }

    public function setValueByName($name, $value)
    {
        if (isset($this->arrProperties[$name])) {
            $this->arrProperties[$name]['value'] = $value;
        }
    }

    public function createPropertiesArrayLoader()
    {
        $loadProperties = [];
        foreach ($this->arrProperties as $property) {
            $loadProperties[$property['code']] = $property['value'];
        }
        return $loadProperties;
    }

    public static function getListValueId($property_code, $list_value)
    {
        $rsListValue = CIBlockProperty::GetPropertyEnum(
            $property_code,
            array(),
            ['VALUE' => trim($list_value)]
        );
        if ($listItem = $rsListValue->GetNext()) {
            return $listItem['ID'];
        } else {
            return false;
        }
    }

    public static function divideMultipleProperties($str, $divider)
    {
        $str = explode($divider, $str);
        foreach ($str as $i=>&$item) {
            $item = trim($item);
            if ($item == '') {
                array_splice($str, $i, 1);
            } 
        }
        unset($item);
        return $str;
    }
}
