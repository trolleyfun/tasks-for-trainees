<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

class StringType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_STRINGTYPE_NAME');
    }

    public function getPropertyFieldHtml($value, array $strHTMLControlName): string
    {
        $result = '';
        if ($this->code && $this->name) {
            $titleValue = htmlspecialcharsbx($this->name);
            $inputName = $strHTMLControlName['VALUE'].'['.htmlspecialcharsbx($this->code).']';
            $inputValue = htmlspecialcharsbx($value);

            $result = '<tr>
                <td align="right">'.$titleValue.': </td>
                <td><input type="text" value="'.$inputValue.'" name="'.$inputName.'"></td>
            </tr>';
        }
        return $result;
    }

    public function getLength($value): bool
    {
        return !$this->isEmpty($value);
    }
}
