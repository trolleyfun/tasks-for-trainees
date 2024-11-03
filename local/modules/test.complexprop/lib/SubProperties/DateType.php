<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

/**
 * Класс для работы со свойством типа "Дата" в составе комплексного свойства.
 */
class DateType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_DATETYPE_NAME');
    }

    public function getPropertyFieldHtml($value, string $name): string
    {
        $result = '';
        if ($this->code && $this->name) {
            $titleValue = htmlspecialcharsbx($this->name);
            $inputName = $name.'['.htmlspecialcharsbx($this->code).']';
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

    /**
     * Проверяет, является ли значение свойства корректной датой.
     *
     * Возвращает true, если значение свойства является датой в формате "dd.mm.yy" или "dd.mm.yyyy".
     * В противном случае возвращает false.
     *
     * @param string $value Значение свойства.
     * @return bool
     */
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
            if (
                !self::isInt($day)
                || !self::isInt($month)
                || !self::isInt($year)
                || !checkdate($month, $day, $year)
            ) {
                $errors[] = Loc::getMessage('COMPLEXPROP_IBLOCK_ERROR_INVALID_DATE');
            }
        }

        return $errors;
    }

    /**
     * Проверяет, является ли значение целым числом.
     *
     * Возвращает true, если значение является целым числом или строкой, которую можно привести к целому числу.
     * Допускаются ведущие нули (например, значение "03" является допустимым). В противном случае возвращает false.
     *
     * @param string|int $value
     * @return bool
     */
    public static function isInt($value)
    {
        return strval($value) == strval(intval($value));
    }
}
