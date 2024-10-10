<?php

function getListValueId($property_code, $list_value)
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

function divideMultipleProperties($str, $divider)
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
