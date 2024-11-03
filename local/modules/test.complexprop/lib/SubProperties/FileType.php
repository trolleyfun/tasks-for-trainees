<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

/**
 * Класс для работы со свойством типа "Файл" в составе комплексного свойства.
 */
class FileType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_FILETYPE_NAME');
    }

    /**
     * Формирует HTML-код для формы редактирования свойства в административном разделе.
     *
     * Значение свойства является массивом вида:
     * ```
     * Array
     * (
     *      ['OLD'] => ID текущего файла
     *      ['NEW'] => массив с параметрами загруженного файла
     *      ['DEL'] => флаг удаления текущего файла
     * )
     * ```
     *
     * В случае отсутствия текущего или загруженного файла соответствующие поля массива отсутствуют.
     * Если нет ни текущего, ни нового файла, значение свойства &ndash; пустая строка.
     *
     * @param mixed $value Значение свойства
     * @param string $name Значение аттрибута "name" полей формы
     * @return string HTML-код формы редактирования свойства
     */
    public function getPropertyFieldHtml($value, string $name): string
    {
        $result = '';
        if ($this->code && $this->name) {
            $fileId = $value['OLD'] ?? '';

            $titleValue = htmlspecialcharsbx($this->name);
            $inputNameOld = $name.'['.htmlspecialcharsbx($this->code).'][OLD]';
            $inputNameNew = $name.'['.htmlspecialcharsbx($this->code).'][NEW]';
            $inputNameDel = $name.'['.htmlspecialcharsbx($this->code).'][DEL]';

            $result = '
                <tr>
                    <td align="right" valign="top">'.$titleValue.': </td>';

            if ($fileId) {
                if ($arFile = \CFile::GetFileArray($fileId)) {
                    if (\CFile::IsImage($arFile['FILE_NAME'])) {
                        $fileHtml = '<img src="'.htmlspecialcharsbx($arFile['SRC']).'">';
                    } else {
                        $fileHtml = '<div class="mf-file-name">'.htmlspecialcharsbx($arFile['FILE_NAME']).'</div>';
                    }

                    $result .= '
                        <td>
                            <table class="mf-img-table">
                                <tr>
                                    <td>'.$fileHtml.'<br>
                                        <div>
                                            <label><input name="'.$inputNameDel.'" value="Y" type="checkbox"> '
                                            .Loc::getMessage("COMPLEXPROP_IBLOCK_EDIT_DELETEFILE_BUTTON_NAME")
                                            .'</label>
                                            <input name="'.$inputNameOld.'" value="'.$fileId.'" type="hidden">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>';
                }
            }

            $result .= '
                <td><input type="file" value="" name="'.$inputNameNew.'"></td>
            </tr>';
        }

        return $result;
    }

     /**
     * Преобразовывает значение свойства перед сохранением в базу данных.
     *
     * Если был загружен новый файл, метод сохраняет новый файл и удаляет текущий файл. Если новый файл
     * не был загружен, метод проверяет, установлен ли флаг удаления текущего файла. Если флаг удаления
     * установлен, текущий файл удаляется.
     *
     * Принимает на вход массив вида:
     * ```
     * Array
     * (
     *      ['OLD'] => ID текущего файла
     *      ['NEW'] => массив с параметрами загруженного файла
     *      ['DEL'] => флаг удаления текущего файла
     * )
     * ```
     * В случае отсутствия текущего или загруженного файла соответствующие поля массива отсутствуют.
     * Если нет ни текущего, ни нового файла, значение свойства &ndash; пустая строка.
     *
     * Возвращает массив:
     * ```
     * Array
     * (
     *      ['OLD'] => ID текущего файла
     * )
     * ```
     * Если текущий файл отсутствует, возвращает пустую строку.
     *
     * @param mixed $value Значение свойства
     * @return mixed Преобразованное значение свойства
     */
    public function onBeforeSave($value): mixed
    {
        $fileId = '';
        if (is_array($value)) {
            if (!empty($value['NEW']['name'])) {
                $fileId = \CFile::SaveFile($value['NEW'], 'iblock');
            }
            if ($fileId || !empty($value['DEL']) && $value['DEL']) {
                if (!empty($value['OLD'])) {
                    \CFile::Delete($value['OLD']);
                }
            } elseif (!$fileId && !empty($value['OLD'])) {
                    $fileId = $value['OLD'];
            }
        }
        if ($this->isEmpty($value)) {
            return '';
        }
        return ['OLD' => $fileId];
    }

    public function getLength($value): bool
    {
        return !$this->isEmpty($value);
    }

    public function isEmpty($value): bool
    {
        $result = empty($value['OLD']) || !empty($value['DEL']) && $value['DEL'];
        $result = $result && empty($value['NEW']['name']);
        return $result;
    }
}
