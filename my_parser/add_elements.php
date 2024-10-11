<?php

$_SERVER['DOCUMENT_ROOT'] = "/opt/lampp/htdocs";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Local\IBlockElementLoader;

\Bitrix\Main\Loader::includeModule('iblock');

define('IBLOCK_ID', 11);
define('FILENAME', 'my_vacancy.csv');

$iBlockElement = new CIBlockElement;
$elementLoader = new IBlockElementLoader;
$elementLoader->initProperties(IBLOCK_ID);

if (($handle = fopen(FILENAME, 'r'))) {
    $data = fgetcsv($handle, separator: ',');
    if ($data = fgetcsv($handle, separator: ',')) {
        $elementLoader->initHeaders($data);

        while ($data = fgetcsv($handle, separator: ',')) {
            $elementLoader->clearElementValues();
            $elementLoader->setElementValues($data, '•');
            $elementLoader->setValueByName('date', date('d.m.Y'));

            $loadIBlockElement = [
                "MODIFIED_BY" => $USER->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => IBLOCK_ID,
                "PROPERTY_VALUES" => $elementLoader->createPropertiesArrayLoader(),
                "NAME" => $elementLoader->elementName,
                "ACTIVE" => 'Y'
            ];            
            $iBlockElement->Add($loadIBlockElement);
        }
    }
    fclose($handle);
}
