<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

class DateType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_DATETYPE_NAME');
    }

    public function getPropertyFieldHtml($value, array $strHTMLControlName): string
    {
        $result = '';
        if ($this->code && $this->name) {
            $titleValue = htmlspecialcharsbx($this->name);
            $inputName = $strHTMLControlName['VALUE'].'['.htmlspecialcharsbx($this->code).']';
            $inputValue = htmlspecialcharsbx($value);

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

    public function onBeforeSave($value): mixed
    {
        return trim($value);
    }

    public function getLength($value): bool
    {
        return !$this->isEmpty($value);
    }

    public function checkFields($value): array
    {
        if (!$value) {
            return array();
        }

        $errors = [];

        $dateArray = explode('.', $value);
        if (count($dateArray) !== 3) {
            $errors[] = Loc::getMessage('COMPLEXPROP_IBLOCK_ERROR_INVALID_DATE');
        } else {
            list($day, $month, $year) = $dateArray;
            if (!my_is_int($day) || !my_is_int($month) || !my_is_int($year) || !checkdate($month, $day, $year)) {
                $errors[] = Loc::getMessage('COMPLEXPROP_IBLOCK_ERROR_INVALID_DATE');
            }
        }

        return $errors;
    }
}
