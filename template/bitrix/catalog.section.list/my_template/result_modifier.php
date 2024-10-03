<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arResult["SECTIONS_COUNT"] > 0) {
    $arSectionsDepth1 = array();
    foreach ($arResult["SECTIONS"] as $arSection) {
        if ($arSection["RELATIVE_DEPTH_LEVEL"] == 1) {
            $arSectionsDepth1[] = $arSection;
        }
    }
    $arResult["SECTIONS"] = $arSectionsDepth1;
    $arResult["SECTIONS_COUNT"] = count($arSectionsDepth1);
}
