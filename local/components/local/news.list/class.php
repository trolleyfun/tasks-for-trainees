<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class NewsListComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_TYPE'] = trim((string)($arParams['IBLOCK_TYPE'] ?? ''));
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['IBLOCK_CODE'] = trim((string)($arParams['IBLOCK_CODE'] ?? ''));

        return $arParams;
    }

    public function executeComponent()
    {
        $elements_filter = ['ACTIVE' => 'Y'];
        if ($this->arParams['IBLOCK_ID']) {
            $elements_filter['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        } elseif ($this->arParams['IBLOCK_CODE']) {
            $elements_filter['IBLOCK_CODE'] = $this->arParams['IBLOCK_CODE'];
        }

        $rsIblockElements = CIBlockElement::getList(array(), $elements_filter);
        while ($element = $rsIblockElements->Fetch()) {
            $this->arResult['ITEMS'][$element['ID']] = $element;
        }

        $this->includeComponentTemplate();
    }
}
