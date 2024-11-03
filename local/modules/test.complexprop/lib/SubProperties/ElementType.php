<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Localization\Loc;

/**
 * Класс для работы со свойством типа "Привязка к элементу инфоблока" в составе комплексного свойства.
 */
class ElementType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_ELEMENTTYPE_NAME');
    }

    /**
     * Формирует HTML-код для формы редактирования свойства в административном разделе.
     *
     * @param int $value ID элемента инфоблока
     * @param string $name Значение аттрибута "name" полей формы
     * @return string HTML-код формы редактирования свойства
     */
    public function getPropertyFieldHtml($value, string $name): string
    {
        $result = '';
        if ($this->code && $this->name) {
            $titleValue = htmlspecialcharsbx($this->name);
            $inputName = $name.'['.htmlspecialcharsbx($this->code).']';
            $code = htmlspecialcharsbx($this->code);

            $elementId = '';
            $elementUrl = '';
            if (!empty($value)) {
                $elementId = $value;
                $arElement = ElementTable::getRow([
                    'filter' => ['ID' => $elementId],
                    'select' => ['ID', 'NAME', 'IBLOCK_ID', 'IBLOCK_TYPE_ID' => 'IBLOCK.IBLOCK_TYPE_ID']
                ]);
                if ($arElement) {
                    $elementUrl = '<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='
                    .$arElement['IBLOCK_ID'].'&ID='.$arElement['ID'].'&type='.$arElement['IBLOCK_TYPE_ID']
                    .'">'.$arElement['NAME'].'</a>';
                }
            }

            $result = '
                <tr>
                    <td align="right">'.$titleValue.': </td>
                    <td>
                        <input name="'.$inputName.'" id="'.$inputName.'" value="'.$elementId.'" size="8"
                            type="text" class="mf-inp-bind-elem">
                        <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n='.$name.'&k='.$code.'\', 900, 700);">&nbsp;
                        <span>'.$elementUrl.'</span>
                    </td>
                </tr>';
        }

        return $result;
    }

    /**
     * Преобразовывает значение свойства перед сохранением в базу данных.
     *
     * @param int $value ID элемента инфоблока
     * @return mixed Преобразованное ID элемента инфоблока
     */
    public function onBeforeSave($value): mixed
    {
        return parent::onBeforeSave($value);
    }

    public function getLength($value): bool
    {
        return !$this->isEmpty($value);
    }

    /**
     * Проверяет корректность введенных пользователем данных.
     *
     * Возвращает массив с текстом ошибок. Если введенные данные корректны, вернет пустой массив.
     *
     * @param int $value ID элемента инфоблока
     * @return bool
     */
    public function checkFields($value): array
    {
        if (!$value) {
            return array();
        }

        $errors = [];

        $arFile = ElementTable::getRow([
            'filter' => ['ID' => $value],
            'select' => ['ID']
        ]);

        if (!$arFile) {
            $errors[] = Loc::getMessage('COMPLEXPROP_IBLOCK_ERROR_INVALID_ELEMENT');
        }

        return $errors;
    }
}
