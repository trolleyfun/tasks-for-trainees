<?php

namespace Test\Complexprop;

use Bitrix\Main\Localization\Loc;
use Test\Complexprop\SubProperties\BaseType;
use Test\Complexprop\SubProperties\DateType;
use Test\Complexprop\SubProperties\EditorType;
use Test\Complexprop\SubProperties\ElementType;
use Test\Complexprop\SubProperties\FileType;
use Test\Complexprop\SubProperties\StringType;

class UserFieldComplexProperty extends \Bitrix\Main\UserField\Types\StringType
{
    public const USER_TYPE_ID = 'COMPLEX';

    protected const PROPERTIES_TYPES = [
        'string' => StringType::class,
        'date' => DateType::class,
        'file' => FileType::class,
        'element' => ElementType::class,
        'editor' => EditorType::class
    ];

    protected static $showedCss = false;
    protected static $showedJs = false;

    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => Loc::getMessage('COMPLEXPROP_USERFIELD_NAME'),
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING
        ];
    }

    public static function renderSettings($userField, ?array $additionalParameters, $varsFromForm): string
    {
        $subProperties = $userField['SETTINGS']['SUBPROPERTIES'] ?? '';
        $subProperties = is_string($subProperties)? unserialize($subProperties): $subProperties;

        self::showCssForSetting();
        self::showJsForSetting($additionalParameters['NAME']);

        $result = '
        <tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">
                <tr valign="top" class="heading mf-setting-title">
                   <td>'.Loc::getMessage('COMPLEXPROP_USERFIELD_SETTINGS_CODEFIELD_NAME').'</td>
                   <td>'.Loc::getMessage('COMPLEXPROP_USERFIELD_SETTINGS_TITLEFIELD_NAME').'</td>
                   <td>'.Loc::getMessage('COMPLEXPROP_USERFIELD_SETTINGS_TYPEFIELD_NAME').'</td>
                </tr>';

        if (is_array($subProperties)) {
            foreach ($subProperties as $prop) {
                if ($prop instanceof BaseType && $prop->getCode()) {
                    $codeName = $additionalParameters['NAME'].'['.htmlspecialcharsbx($prop->getCode()).'][CODE]';
                    $codeValue = htmlspecialcharsbx($prop->getCode());
                    $titleName = $additionalParameters['NAME'].'['.htmlspecialcharsbx($prop->getCode()).'][TITLE]';
                    $titleValue = htmlspecialcharsbx($prop->getName());
                    $typeName = $additionalParameters['NAME'].'['.htmlspecialcharsbx($prop->getCode()).'][TYPE]';
                    $result .= '
                        <tr valign="top">
                            <td><input type="text" class="inp-code" size="20" name="'.$codeName.'"
                                value="'.$codeValue.'"></td>
                            <td><input type="text" class="inp-title" size="35" name="'.$titleName.'"
                                value="'.$titleValue.'"></td>
                            <td>
                                <select class="inp-type" name="'.$typeName.'">
                                    '.self::getPropertyTypesList($prop->getTypeCode()).'
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
                <input type="hidden" name="MULTIPLE" value="Y">
            </td></tr>
            <tr><td colspan="2" style="text-align: center;">
                <input type="button"
                    value="'.Loc::getMessage('COMPLEXPROP_USERFIELD_SETTINGS_ADDBUTTON_NAME').'"
                    onclick="addNewRows()">
            </td></tr>
        </td></tr>';

        return $result;
    }

    public static function prepareSettings(array $userField): array
    {
        $subProperties = $userField['SETTINGS'] ?? '';
        if (!is_array($subProperties)) {
            $subProperties = array();
        } else {
            foreach ($subProperties as &$prop) {
                $className = self::PROPERTIES_TYPES[$prop['TYPE']] ?? '';
                if (
                    empty($prop['CODE'])
                    || empty($prop['TITLE'])
                    || empty($prop['TYPE'])
                    || !class_exists($className)
                ) {
                    unset($prop);
                } else {
                    $prop = new $className(
                        trim($prop['CODE']),
                        trim($prop['TITLE']),
                        $prop['TYPE']
                    );
                }
            }
        }
        return ['SUBPROPERTIES' => serialize($subProperties)];
    }

    public static function renderEditForm(array $userField, ?array $additionalParameters): string
    {
        $subProperties = $userField['SETTINGS']['SUBPROPERTIES'] ?? '';
        $subProperties = is_string($subProperties)? unserialize($subProperties): $subProperties;

        $value = $additionalParameters['VALUE'][0] ?? '';
        $value = is_string($value)? unserialize($value): $value;

        $inputName = $additionalParameters['NAME'] ?? '';

        self::showCss();
        self::showJs();

        $result = '<div class="mf-gray"><a class="cl mf-toggle">'
        .Loc::getMessage('COMPLEXPROP_USERFIELD_EDIT_HIDEBUTTON_NAME').'</a></div>';

        $result .= '<table class="mf-fields-list active">';

        if (is_array($subProperties) && $inputName) {
            foreach ($subProperties as $prop) {
                if ($prop instanceof BaseType) {
                    $val = $value[$prop->getCode()] ?? '';
                    $result .= $prop->getPropertyFieldHtml($val, $inputName.'[0]');
                }
            }
        }

        $result .= '</table>';

        return $result;
    }

    public static function onBeforeSave($userField, $value)
    {
        $subProperties = $userField['SETTINGS']['SUBPROPERTIES'] ?? '';
        $subProperties = is_string($subProperties)? unserialize($subProperties): $subProperties;

        $isEmpty = true;
        if (is_array($value) && is_array($subProperties)) {
            foreach ($value as $code=>&$val) {
                if (!empty($subProperties[$code]) && $subProperties[$code] instanceof BaseType) {
                    $val = $subProperties[$code]->onBeforeSave($val);
                    $isEmpty = $isEmpty && $subProperties[$code]->isEmpty($val);
                }
            }
        }

        $result = '';
        if (!$isEmpty) {
            $result = serialize($value);
        }

        return $result;
    }

    public static function checkFields(array $userField, $value): array
    {
        $errors = [];

        $subProperties = $userField['SETTINGS']['SUBPROPERTIES'] ?? '';
        $subProperties = is_string($subProperties)? unserialize($subProperties): $subProperties;

        if (is_array($value) && is_array($subProperties)) {
            foreach ($value as $code=>$val) {
                if (!empty($subProperties[$code]) && $subProperties[$code] instanceof BaseType) {
                    $err = $subProperties[$code]->checkFields($val);
                    foreach ($err as &$msg) {
                        $msg = ['id' => $subProperties[$code]->getTypeCode(), 'text' => $msg];
                    }
                    $errors = array_merge($errors, $err);
                }
            }
        }

        return $errors;
    }

    protected static function getPropertyTypesList($selectedType = '')
    {
        $result = '<option value="">'.Loc::getMessage('COMPLEXPROP_USERFIELD_SETTINGS_TYPEOPTION').'</option>';
        foreach (self::PROPERTIES_TYPES as $code=>$type) {
            if (class_exists($type)) {
                $typeTitle = htmlspecialcharsbx($type::getTypeName());
                $typeValue = htmlspecialcharsbx($code);
                $selected = '';
                if ($code === $selectedType) {
                    $selected = 'selected';
                }
                $result .= "<option value=\"{$typeValue}\" {$selected}>{$typeTitle}</option>";
            }
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
                        $(this).text('<?=Loc::getMessage('COMPLEXPROP_USERFIELD_EDIT_HIDEBUTTON_NAME')?>');
                    }
                    else{
                        $(this).text('<?=Loc::getMessage('COMPLEXPROP_USERFIELD_EDIT_SHOWBUTTON_NAME')?>');
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
