<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

/**
 * Класс для работы со свойством типа "Строка" в составе комплексного свойства.
 */
class StringType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_STRINGTYPE_NAME');
    }

    public function getPropertyFieldHtml($value, string $name): string
    {
        $result = '';
        if ($this->code && $this->name) {
            $titleValue = htmlspecialcharsbx($this->name);
            $inputName = $name.'['.htmlspecialcharsbx($this->code).']';
            $inputValue = htmlspecialcharsbx($value);

            $result = '<tr>
                <td align="right">'.$titleValue.': </td>
                <td><input type="text" value="'.$inputValue.'" name="'.$inputName.'"></td>
            </tr>';
        }
        return $result;
    }

    public function onBeforeSave($value): mixed
    {
        return trim($value);
    }

    public function getLength($value): bool
    {
        return !$this->isEmpty($value);
    }
}
