<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

/**
 * Класс для работы со свойством типа "HTML/Визуальный редактор" в составе комплексного свойства.
 */
class EditorType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_EDITORTYPE_NAME');
    }

    /**
     * Формирует HTML-код для формы редактирования свойства в административном разделе.
     *
     * Значение свойства является массивом вида:
     * ```
     * Array
     * (
     *      ['TEXT'] => текст_свойства
     *      ['TYPE'] => тип_текста
     * )
     * ```
     *
     * Тип текста может принимать два значения: "text" и "html".
     *
     * Если текст отсутствует, значение свойства &ndash; пустая строка.
     *
     * @param mixed $value Значение свойства
     * @param string $name Значение аттрибута "name" полей формы
     * @return string HTML-код формы редактирования свойства
     */
    public function getPropertyFieldHtml($value, string $name): string
    {
        $result = '';
        if ($this->code && $this->name) {
            $titleValue = htmlspecialcharsbx($this->name);
            $inputNameText = $name.'['.htmlspecialcharsbx($this->code).'][TEXT]';
            $inputNameType = $name.'['.htmlspecialcharsbx($this->code).'][TYPE]';

            if (empty($value['TEXT'])) {
                $inputValueText = '';
            } else {
                $inputValueText = htmlspecialcharsbx($value['TEXT']);
            }
            if (empty($value['TYPE'])) {
                $inputValueType = 'text';
            } else {
                $inputValueType = $value['TYPE'] === 'html'? 'html': 'text';
            }

            $result = '
                <tr>
                    <td align="right" valign="top">'.$titleValue.': </td>
                    <td>';

            ob_start();
            self::AddHTMLEditorFrame(
                $inputNameText,
                $inputValueText,
                $inputNameType,
                $inputValueType
            );
            $result .= ob_get_contents();
            ob_end_clean();

            $result .= '</td></tr>';
        }

        return $result;
    }

    /**
     * Преобразовывает значение свойства перед сохранением в базу данных.
     *
     * Значение свойства является массивом вида:
     * ```
     * Array
     * (
     *      ['TEXT'] => текст_свойства
     *      ['TYPE'] => тип_текста
     * )
     * ```
     *
     * Тип текста может принимать два значения: "text" и "html".
     *
     * Если текст отсутствует, возвращает пустую строку.
     *
     * @param mixed $value Значение свойства
     * @return mixed Преобразованное значение свойства
     */
    public function onBeforeSave($value): mixed
    {
        if (!empty($value['TEXT']) && !empty($value['TYPE']) && $value['TYPE'] === 'text') {
            $value['TEXT'] = trim($value['TEXT']);
        }
        if ($this->isEmpty($value)) {
            return '';
        }
        return $value;
    }

    public function getLength($value): bool
    {
        return !$this->isEmpty($value);
    }

    public function isEmpty($value): bool
    {
        if (!empty($value['TEXT']) && !empty($value['TYPE']) && $value['TYPE'] === 'html') {
            $text = trim(strip_tags($value['TEXT']));
            return !(bool)$text;
        }
        return true;
    }

    /**
     * Выводит HTML-код HTML/Визуального редактора.
     *
     * Модификация метода CFileMan::AddHTMLEditorFrame. По сравнению с оригинальным методом изменено
     * значение аттрибута "name" у текстового поля.
     *
     * @param string $strTextFieldName Значение аттрибута "name" текстового поля
     * @param string $strTextValue Значение текстового поля
     * @param string $strTextTypeFieldName Значение аттрибута "name" переключателя режимов редактора
     * @param string $strTextTypeValue Режим редактора ("text" или "html")
     * @return void
     */
    protected static function AddHTMLEditorFrame(
        $strTextFieldName,
        $strTextValue,
        $strTextTypeFieldName,
        $strTextTypeValue,
        $arSize = Array("height"=>350),
        $CONVERT_FOR_WORKFLOW="N",
        $WORKFLOW_DOCUMENT_ID=0,
        $NEW_DOCUMENT_PATH="",
        $textarea_field="",
        $site = false,
        $bWithoutPHP = true,
        $arTaskbars = false,
        $arAdditionalParams = Array()
    )
    {
        // We have to avoid of showing HTML-editor with probably unsecure content when loosing the session [mantis:#0007986]
        if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
            return;

        global $htmled, $usehtmled;
        $strTextFieldId = preg_replace("/[^a-zA-Z0-9_:\.]/is", "", $strTextFieldName);

        if(is_array($arSize))
            $iHeight = $arSize["height"];
        else
            $iHeight = $arSize;

        $strTextValue = htmlspecialcharsback($strTextValue);
        $dontShowTA = isset($arAdditionalParams['dontshowta']) ? $arAdditionalParams['dontshowta'] : false;

        if ($arAdditionalParams['hideTypeSelector'] ?? null)
        {
            $textType = $strTextTypeValue == 'html' ? 'editor' : 'text';
            ?><input type="hidden" name="<?= $strTextTypeFieldName?>" value="<?= $strTextTypeValue?>"/><?
        }
        else
        {
            $textType = \CFileMan::ShowTypeSelector(array(
                'name' => $strTextFieldId,
                'key' => ($arAdditionalParams['saveEditorKey'] ?? null),
                'strTextTypeFieldName' => $strTextTypeFieldName,
                'strTextTypeValue' => $strTextTypeValue,
                'bSave' => ($arAdditionalParams['saveEditorState'] ?? null) !== false
            ));
        }

        $curHTMLEd = $textType == 'editor';
        setEditorEventHandlers($strTextFieldId);
        ?>
        <textarea class="typearea" style="<? echo(($curHTMLEd || $dontShowTA) ? 'display:none;' : '');?>width:100%;height:<?=$iHeight?>px;" name="<?=htmlspecialcharsbx($strTextFieldName)?>" id="bxed_<?=$strTextFieldId?>" wrap="virtual" <?=$textarea_field?>><?= htmlspecialcharsbx($strTextValue)?></textarea>
        <?

        if ($bWithoutPHP)
            $arTaskbars = Array("BXPropertiesTaskbar", "BXSnippetsTaskbar");
        else if (!$arTaskbars)
            $arTaskbars = Array("BXPropertiesTaskbar", "BXSnippetsTaskbar", "BXComponents2Taskbar");

        $minHeight = ($arAdditionalParams['minHeight'] ?? null) ? intval($arAdditionalParams['minHeight']) : 450;
        $arParams = Array(
            "bUseOnlyDefinedStyles"=>\COption::GetOptionString("fileman", "show_untitled_styles", "N")!="Y",
            "bFromTextarea" => true,
            "bDisplay" => $curHTMLEd,
            "bWithoutPHP" => $bWithoutPHP,
            "arTaskbars" => $arTaskbars,
            "height" => max($iHeight, $minHeight)
        );

        if (isset($arAdditionalParams['use_editor_3']))
            $arParams['use_editor_3'] = $arAdditionalParams['use_editor_3'];

        $arParams['site'] = ($site == ''?LANG:$site);
        if(isset($arSize["width"]))
            $arParams["width"] = $arSize["width"];

        if (isset($arAdditionalParams))
            $arParams["arAdditionalParams"] = $arAdditionalParams;

        if (isset($arAdditionalParams['limit_php_access']))
            $arParams['limit_php_access'] = $arAdditionalParams['limit_php_access'];

        if (isset($arAdditionalParams['toolbarConfig']))
            $arParams['toolbarConfig'] = $arAdditionalParams['toolbarConfig'];

        if (isset($arAdditionalParams['componentFilter']))
            $arParams['componentFilter'] = $arAdditionalParams['componentFilter'];

        $arParams['setFocusAfterShow'] = isset($arParams['setFocusAfterShow']) ? $arParams['setFocusAfterShow'] : false;

        \CFileMan::ShowHTMLEditControl($strTextFieldId, $strTextValue, $arParams);
    }
}
