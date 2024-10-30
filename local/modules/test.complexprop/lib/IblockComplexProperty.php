<?php

namespace Test\Complexprop;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class IblockComplexProperty
{
    protected const PROPERTIES_TYPES = [
        'string' => [
            'TITLE_CODE' => 'COMPLEXPROP_IBLOCK_STRINGTYPE_NAME',
            'METHOD' => 'getStringPropertyTypeHtml'
        ],
        'date' => [
            'TITLE_CODE' => 'COMPLEXPROP_IBLOCK_DATETYPE_NAME',
            'METHOD' => 'getDatePropertyTypeHtml'
        ],
        'file' => [
            'TITLE_CODE' => 'COMPLEXPROP_IBLOCK_FILETYPE_NAME',
            'METHOD' => 'getFilePropertyTypeHtml'
        ],
        'element' => [
            'TITLE_CODE' => 'COMPLEXPROP_IBLOCK_ELEMENTTYPE_NAME',
            'METHOD' => 'getElementPropertyTypeHtml'
        ]
    ];

    protected static $showedCss = false;
    protected static $showedJs = false;

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
            'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml']
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

        if (isset($arProperty['USER_TYPE_SETTINGS'])) {
            $subProperties = $arProperty['USER_TYPE_SETTINGS'];
        } else {
            $subProperties = '';
        }

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
                if (!empty($prop['CODE']) && !empty($prop['TITLE']) && !empty($prop['TYPE'])) {
                    $codeName = $strHTMLControlName['NAME'].'['.htmlspecialcharsbx($code).'][CODE]';
                    $codeValue = htmlspecialcharsbx($code);
                    $titleName = $strHTMLControlName['NAME'].'['.htmlspecialcharsbx($code).'][TITLE]';
                    $titleValue = htmlspecialcharsbx($prop['TITLE']);
                    $typeName = $strHTMLControlName['NAME'].'['.htmlspecialcharsbx($code).'][TYPE]';
                    $result .= '
                        <tr valign="top">
                            <td><input type="text" class="inp-code" size="20" name="'.$codeName.'"
                                value="'.$codeValue.'"></td>
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
                if (
                    empty($prop['CODE'])
                    || empty($prop['TITLE'])
                    || empty($prop['TYPE'])
                    || !in_array($prop['TYPE'], array_keys(self::PROPERTIES_TYPES))
                ) {
                    unset($prop);
                }
            }
        }
        return $subProperties;
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        if (!isset($arProperty['USER_TYPE_SETTINGS'])) {
            return '';
        } else {
            $subProperties = $arProperty['USER_TYPE_SETTINGS'];
        }

        self::showCss();
        self::showJs();

        $result = '<div class="mf-gray"><a class="cl mf-toggle">'
        .Loc::getMessage('COMPLEXPROP_IBLOCK_EDIT_HIDEBUTTON_NAME').'</a>';
        if($arProperty['MULTIPLE'] === 'Y'){
            $result .= ' | <a class="cl mf-delete">'.
            Loc::getMessage('COMPLEXPROP_IBLOCK_EDIT_CLEARBUTTON_NAME').'</a></div>';
        }

        $result .= '<table class="mf-fields-list active">';

        if (is_array($subProperties)) {
            foreach ($subProperties as $prop) {
                if (!empty($prop['TYPE']) && !empty(self::PROPERTIES_TYPES[$prop['TYPE']])) {
                    $method = self::PROPERTIES_TYPES[$prop['TYPE']]['METHOD'];
                    if (method_exists(__CLASS__, $method)) {
                        $result .= self::$method($prop, $value, $strHTMLControlName);
                    }
                }
            }
        }

        $result .= '</table>';

        return $result;
    }

    protected static function getStringPropertyTypeHtml($settings, $value, $strHTMLControlName)
    {
        if (empty($settings['CODE']) || empty($settings['TITLE'])) {
            $result = '';
        } else {
            $titleValue = htmlspecialcharsbx($settings['TITLE']);
            $inputName = $strHTMLControlName['VALUE'].'['.htmlspecialcharsbx($settings['CODE']).']';
            if (!empty($value['VALUE'][$settings['CODE']])) {
                $inputValue = htmlspecialcharsbx($value['VALUE'][$settings['CODE']]);
            } else {
                $inputValue = '';
            }

            $result = '<tr>
                <td align="right">'.$titleValue.': </td>
                <td><input type="text" value="'.$inputValue.'" name="'.$inputName.'"></td>
            </tr>';
        }
        return $result;
    }

    public static function getDatePropertyTypeHtml($settings, $value, $strHTMLControlName)
    {
        if (empty($settings['CODE']) || empty($settings['TITLE'])) {
            $result = '';
        } else {
            $titleValue = htmlspecialcharsbx($settings['TITLE']);
            $inputName = $strHTMLControlName['VALUE'].'['.htmlspecialcharsbx($settings['CODE']).']';
            if (!empty($value['VALUE'][$settings['CODE']])) {
                $inputValue = htmlspecialcharsbx($value['VALUE'][$settings['CODE']]);
            } else {
                $inputValue = '';
            }

            $result = '<tr>
                        <td align="right" valign="top">'.$titleValue.': </td>
                        <td>
                            <table>
                                <tr>
                                    <td style="padding: 0;">
                                        <div class="adm-input-wrap adm-input-wrap-calendar">
                                            <input class="adm-input adm-input-calendar" type="text"
                                                name="'.$inputName.'" size="23" value="'.$inputValue.'">
                                            <span class="adm-calendar-icon"
                                                onclick="BX.calendar({node: this, field:\''.$inputName
                                                .'\', form: \'\', bTime: true, bHideTime: false});"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
        }
        return $result;
    }

    public static function getFilePropertyTypeHtml($settings, $value, $strHTMLControlName)
    {

    }

    public static function getElementPropertyTypeHtml($settings, $value, $strHTMLControlName)
    {

    }

    protected static function getPropertyTypesList($selectedType = '')
    {
        $result = '<option value="">'.Loc::getMessage('COMPLEXPROP_IBLOCK_SETTINGS_TYPEOPTION').'</option>';
        foreach (self::PROPERTIES_TYPES as $code=>$type) {
            $typeTitle = htmlspecialcharsbx(Loc::getMessage($type['TITLE_CODE']));
            $typeValue = htmlspecialcharsbx($code);
            $selected = '';
            if ($code === $selectedType) {
                $selected = 'selected';
            }
            $result .= "<option value=\"{$typeValue}\" {$selected}>{$typeTitle}</option>";
        }
        return $result;
    }

    protected static function showJsForSetting($inputName)
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
                        $(this).closest('tr').find('input.inp-code').removeAttr('name');
                        $(this).closest('tr').find('input.inp-title').removeAttr('name');
                        $(this).closest('tr').find('select.inp-type').removeAttr('name');
                    }
                    else{
                        $(this).closest('tr').find('input.inp-code').attr('name', '<?=$inputName?>[' + code + '][CODE]');
                        $(this).closest('tr').find('input.inp-title').attr('name', '<?=$inputName?>[' + code + '][TITLE]');
                        $(this).closest('tr').find('select.inp-type').attr('name', '<?=$inputName?>[' + code + '][TYPE]');
                    }
                });
            </script>
            <?php
        }
    }

    protected static function showCssForSetting()
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
            <?php
        }
    }

    protected static function showCss()
    {
        if(!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .cl {cursor: pointer;}
                .mf-gray {color: #797777;}
                .mf-fields-list {display: none; padding-top: 10px; margin-bottom: 10px!important; margin-left: -300px!important; border-bottom: 1px #e0e8ea solid!important;}
                .mf-fields-list.active {display: block;}
                .mf-fields-list td {padding-bottom: 5px;}
                .mf-fields-list td:first-child {width: 300px; color: #616060;}
                .mf-fields-list td:last-child {padding-left: 5px;}
                .mf-fields-list input[type="text"] {width: 350px!important;}
                .mf-fields-list textarea {min-width: 350px; max-width: 650px; color: #000;}
                .mf-fields-list img {max-height: 150px; margin: 5px 0;}
                .mf-img-table {background-color: #e0e8e9; color: #616060; width: 100%;}
                .mf-fields-list input[type="text"].adm-input-calendar {width: 170px!important;}
                .mf-file-name {word-break: break-word; padding: 5px 5px 0 0; color: #101010;}
                .mf-fields-list input[type="text"].mf-inp-bind-elem {width: unset!important;}
            </style>
            <?php
        }
    }

    protected static function showJs()
    {
        \CJSCore::Init(array("jquery"));
        if(!self::$showedJs) {
            self::$showedJs = true;
            ?>
            <script>
                $(document).on('click', 'a.mf-toggle', function (e) {
                    e.preventDefault();

                    var table = $(this).closest('tr').find('table.mf-fields-list');
                    $(table).toggleClass('active');
                    if($(table).hasClass('active')){
                        $(this).text('<?=Loc::getMessage('COMPLEXPROP_IBLOCK_EDIT_HIDEBUTTON_NAME')?>');
                    }
                    else{
                        $(this).text('<?=Loc::getMessage('COMPLEXPROP_IBLOCK_EDIT_SHOWBUTTON_NAME')?>');
                    }
                });

                $(document).on('click', 'a.mf-delete', function (e) {
                    e.preventDefault();

                    var textInputs = $(this).closest('tr').find('input[type="text"]');
                    $(textInputs).each(function (i, item) {
                        $(item).val('');
                    });

                    var checkBoxInputs = $(this).closest('tr').find('input[type="checkbox"]');
                    $(checkBoxInputs).each(function (i, item) {
                        $(item).attr('checked', 'checked');
                    });

                    $(this).closest('tr').hide('slow');
                });
            </script>
            <?php
        }
    }
}
