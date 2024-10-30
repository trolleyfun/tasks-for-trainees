<?php

namespace Test\Complexprop;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class IblockComplexProperty
{
    private static $showedCss = false;
    private static $showedJs = false;

    public static function GetUserTypeDescription()
    {
        if (!Loader::includeModule('iblock')) {
            return array();
        }
        return [
            'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
            'USER_TYPE' => 'COMPLEX',
            'DESCRIPTION' => Loc::getMessage('COMPLEXPROP_IBLOCK_NAME'),
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            'PrepareSettings' => [__CLASS__, 'PrepareSettings']
        ];
    }

    public static function ConvertToDB($arProperty, $value)
    {
        return $result = [
            'VALUE' => json_encode($value['VALUE']),
            'DESCRIPTION' => $value['DESCRIPTION']
        ];
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        $result = [
            'VALUE' => json_decode($value['VALUE'], true),
            'DESCRIPTION' => $value['DESCRIPTION']
        ];
        if (is_null($result['VALUE'])) {
            $result['VALUE'] = array();
        }
        return $result;
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $arPropertyFields = [
            'USER_TYPE_SETTINGS_TITLE' => Loc::getMessage('COMPLEXPROP_IBLOCK_SETTINGS_TITLE')
        ];

        $subProperties = $arProperty['USER_TYPE_SETTINGS'];

        self::showCssForSetting();
        self::showJsForSetting($strHTMLControlName['NAME']);

        $result = '
        <tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">
                <tr valign="top" class="heading mf-setting-title">
                   <td>'.Loc::getMessage('COMPLEXPROP_IBLOCK_SETTINGS_CODEFIELD_NAME').'</td>
                   <td>'.Loc::getMessage('COMPLEXPROP_IBLOCK_SETTINGS_TITLEFIELD_NAME').'</td>
                   <td>'.Loc::getMessage('COMPLEXPROP_IBLOCK_SETTINGS_TYPEFIELD_NAME').'</td>
                </tr>';

        if (is_array($subProperties)) {
            foreach ($subProperties as $code=>$prop) {
                if (!empty($prop['TITLE']) && !empty($prop['TYPE'])) {
                    $codeValue = htmlspecialcharsbx($code);
                    $titleName = $strHTMLControlName['NAME'].'['.htmlspecialcharsbx($code).'][TITLE]';
                    $titleValue = htmlspecialcharsbx($prop['TITLE']);
                    $typeName = $strHTMLControlName['NAME'].'['.htmlspecialcharsbx($code).'][TYPE]';
                    $result .= '
                        <tr valign="top">
                            <td><input type="text" class="inp-code" size="20" value="'.$codeValue.'"></td>
                            <td><input type="text" class="inp-title" size="35" name="'.$titleName.'"
                                value="'.$titleValue.'"></td>
                            <td>
                                <select class="inp-type" name="'.$typeName.'">
                                    '.self::getPropertyTypesList($prop['TYPE']).'
                                </select>
                            </td>
                        </tr>';
                }
            }
        }

        $result .= '
                <tr valign="top">
                    <td><input type="text" class="inp-code" size="20"></td>
                    <td><input type="text" class="inp-title" size="35"></td>
                    <td>
                        <select class="inp-type"> '.self::getPropertyTypesList().'</select>
                    </td>
                </tr>
            </table>';

        $result .= '
            <tr><td colspan="2" style="text-align: center;">
                <input type="button"
                    value="'.Loc::getMessage('COMPLEXPROP_IBLOCK_SETTINGS_ADDBUTTON_NAME').'"
                    onclick="addNewRows()">
            </td></tr>
        </td></tr>';

        return $result;
    }

    public static function PrepareSettings($arProperty)
    {
        $subProperties = $arProperty['USER_TYPE_SETTINGS'];
        if (!is_array($subProperties)) {
            $subProperties = array();
        } else {
            foreach ($subProperties as &$prop) {
                if (empty($prop['TITLE']) || empty($prop['TYPE'])) {
                    unset($prop);
                }
            }
        }
        return $subProperties;
    }

    private static function getPropertyTypesList($selectedType = '')
    {
        $propertyTypes = [
            'string' => Loc::getMessage('COMPLEXPROP_IBLOCK_STRINGTYPE_NAME'),
            'date' => Loc::getMessage('COMPLEXPROP_IBLOCK_DATETYPE_NAME'),
            'file' => Loc::getMessage('COMPLEXPROP_IBLOCK_FILETYPE_NAME'),
            'element' => Loc::getMessage('COMPLEXPROP_IBLOCK_ELEMENTTYPE_NAME')
        ];
        $result = '';
        foreach ($propertyTypes as $code=>$type) {
            $typeTitle = htmlspecialcharsbx($type);
            $typeValue = htmlspecialcharsbx($code);
            $selected = '';
            if ($code === $selectedType) {
                $selected = 'selected';
            }
            $result .= "<option value=\"{$typeValue}\" {$selected}>{$typeTitle}</option>";
        }
        return $result;
    }

    private static function showJsForSetting($inputName)
    {
        \CJSCore::Init(array("jquery"));
        if (!self::$showedJs) {
            self::$showedJs = true;
            ?>
            <script>
                function addNewRows() {
                    $("#many-fields-table").append('' +
                        '<tr valign="top">' +
                        '<td><input type="text" class="inp-code" size="20"></td>' +
                        '<td><input type="text" class="inp-title" size="35"></td>' +
                        '<td><select class="inp-type"><?=self::getPropertyTypesList()?></select></td>' +
                        '</tr>');
                }

                $(document).on('change', '.inp-code', function(){
                    var code = $(this).val();

                    if(code.length <= 0){
                        $(this).closest('tr').find('input.inp-title').removeAttr('name');
                        $(this).closest('tr').find('select.inp-type').removeAttr('name');
                    }
                    else{
                        $(this).closest('tr').find('input.inp-title').attr('name', '<?=$inputName?>[' + code + '][TITLE]');
                        $(this).closest('tr').find('select.inp-type').attr('name', '<?=$inputName?>[' + code + '][TYPE]');
                    }
                });
            </script>
            <?php
        }
    }

    private static function showCssForSetting()
    {
        if(!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .many-fields-table {margin: 0 auto; /*display: inline;*/}
                .mf-setting-title td {text-align: center!important; border-bottom: unset!important;}
                .many-fields-table td {text-align: center;}
                .many-fields-table > input, .many-fields-table > select{width: 90%!important;}
                .inp-sort{text-align: center;}
                .inp-type{min-width: 125px;}
            </style>
            <?
        }
    }
}
