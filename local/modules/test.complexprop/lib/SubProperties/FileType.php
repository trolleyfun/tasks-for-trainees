<?php

namespace Test\Complexprop\SubProperties;

use Bitrix\Main\Localization\Loc;

class FileType extends BaseType
{
    public static function getTypeName(): string
    {
        return Loc::getMessage('COMPLEXPROP_SUBPROPERTY_FILETYPE_NAME');
    }

    public function getPropertyFieldHtml($value, array $strHTMLControlName): string
    {
        $result = '';
        if ($this->code && $this->name) {
            $fileId = $value['OLD'] ?? '';

            $titleValue = htmlspecialcharsbx($this->name);
            $inputNameOld = $strHTMLControlName['VALUE'].'['.htmlspecialcharsbx($this->code).'][OLD]';
            $inputNameNew = $strHTMLControlName['VALUE'].'['.htmlspecialcharsbx($this->code).'][NEW]';
            $inputNameDel = $strHTMLControlName['VALUE'].'['.htmlspecialcharsbx($this->code).'][DEL]';

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
