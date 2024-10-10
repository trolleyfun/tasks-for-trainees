<?php

$_SERVER['DOCUMENT_ROOT'] = "/opt/lampp/htdocs";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require_once("includes/functions.php");

\Bitrix\Main\Loader::includeModule('iblock');

$iblock_id = 11;
$filename = 'my_vacancy.csv';
$iBlockElement = new CIBlockElement;

$rsProperties = CIBlock::getProperties($iblock_id, array(), array());
$arProperties = [];
while ($property = $rsProperties->GetNext()) {
    $key = trim(strtolower($property['CODE']));
    $arProperties[$key]['code'] = $property['CODE'];
    $arProperties[$key]['type'] = $property['PROPERTY_TYPE'];
    $arProperties[$key]['multiple'] = $property['MULTIPLE'];
    $arProperties[$key]['value'] = '';
}

if (($handle = fopen($filename, 'r'))) {
    $data = fgetcsv($handle, separator: ',');
    if ($data = fgetcsv($handle, separator: ',')) {
        $columnHeaders = [];
        foreach ($data as $header) {
            $columnHeaders[] = trim(strtolower($header));
        }
        while ($data = fgetcsv($handle, separator: ',')) {
            foreach ($arProperties as $property) {
                $property['value'] = '';
            }
            $element_name = '';

            foreach ($data as $i=>$value) {
                if (isset($columnHeaders[$i])) {
                    $key = $columnHeaders[$i];
                    $value = str_replace(["\r\n", "\n", "\r"], ' ', $value);
                    if ($key == 'name') {
                        $element_name = trim($value);
                    } elseif (isset($arProperties[$columnHeaders[$i]])) {
                        if ($arProperties[$key]['multiple'] == 'Y') {
                            $value = divideMultipleProperties($value, '•');
                            if ($arProperties[$key]['type'] == 'L') {
                                foreach ($value as $i=>&$item) {
                                    $id = getListValueId($arProperties[$key]['code'], $item);
                                    if ($id) {
                                        $item = $id;
                                    } else {
                                        $item = '';
                                    }
                                }
                            }
                            unset($item);
                        } elseif ($arProperties[$key]['type'] == 'L') {
                            $id = getListValueId($arProperties[$key]['code'], $value);
                            if ($id) {
                                $value = $id;
                            } else {
                                $value = '';
                            }
                        } else {
                            $value = trim($value);
                        }
                        $arProperties[$key]['value'] = $value;
                    }
                }
            }

            if (isset($arProperties['date'])) {
                $arProperties['date']['value'] = date('d.m.Y');
            }

            $loadProperties = [];
            foreach ($arProperties as $property) {
                $loadProperties[$property['code']] = $property['value'];
            }

            $loadIBlockElement = [
                "MODIFIED_BY" => $USER->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => $iblock_id,
                "PROPERTY_VALUES" => $loadProperties,
                "NAME" => $element_name,
                "ACTIVE" => 'Y'
            ];            
            $iBlockElement->Add($loadIBlockElement);
        }
    }

    fclose($handle);
}
