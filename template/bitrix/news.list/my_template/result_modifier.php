<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$arItemsOfSection = array();
foreach ($arResult["ITEMS"] as $arItem) {
    if ($arItem["IBLOCK_SECTION_ID"] == $arParams["PARENT_SECTION"]) {
        $arItemsOfSection[] = $arItem;
    }
    $arResult["ITEMS"] = $arItemsOfSection;
}
