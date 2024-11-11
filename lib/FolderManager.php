<?php

namespace Trolleyfun\Yandex;

use Arhitector\Yandex\Disk\Operation;
use Trolleyfun\Yandex\Exception\ResourceTypeNotValidException;

/**
 * Класс для управлениями директориями на яндекс-диске.
 */
class FolderManager extends DiskManager
{
    /**
     * @param string $oauthToken
     * @param string $resourcePath
     * @throws Trolleyfun\Yandex\Exception\ResourceTypeNotValidException
     *      если ресурс не является папкой.
     * @return void
     */
    public function __construct($oauthToken, $resourcePath)
    {
        parent::__construct($oauthToken, $resourcePath);
        if (!$this->resource->isDir()) {
            throw new ResourceTypeNotValidException('Ресурс не является папкой');
        }
    }

    /**
     * Проверяет, является ли ресурс корневой директорией диска.
     *
     * Возвращает true, если ресурс является корневой директорией на диске. Возвращает false, если ресурс
     * не является корневой директорией на диске.
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->resource->get('path') === 'disk:/' || $this->resource->get('path') === '/';
    }

    /**
     * Формирует HTML-код списка элементов директории яндекс-диска.
     *
     * @return string HTML-код списка элементов
     */
    public function displayItems()
    {
        $result = '';

        if ($parentPath = $this->getParentPath()) {
            $result .= self::getFolderHtml($parentPath, '..', true);
        }

        foreach ($this->resource->get('items') as $item) {
            if ($item->isDir()) {
                $result .= self::getFolderHtml($item->get('path'), $item->get('name'));
            } elseif ($item->isFile()) {
                $result .= self::getFileHtml($item->get('path'), $item->get('name'), $item->get('mime_type'));
            }
        }

        return $result;
    }

    /**
     * Создает новую папку в текущем каталоге яндекс диска.
     *
     * Возвращает true, если новая папка создана. Возвращает false, если не удалось создать новую папку.
     *
     * Если ресурс с таким именем уже существует, папка создана не будет.
     *
     * @param string $name Имя новой папки
     * @return bool
     */
    public function createFolder($name)
    {
        $status = false;
        if (trim($name)) {
            $parentPath = $this->resource->get('path');
            if ($parentPath[-1] === '/') {
                $newPath = $parentPath . trim($name);
            } else {
                $newPath = $parentPath . '/' . trim($name);
            }

            $newResource = $this->disk->getResource($newPath);
            $newResource->create();

            $status = true;
        }

        return $status;
    }

    /**
     * Загружает файл в текущую директорию яндекс-диска.
     *
     * Входной параметр должен быть массивом описывающим файл:
     * ```
     * Array
     * (
     *      [name] => имя_файла
     *      [type] => тип_файла
     *      [tmp_name] => путь_к_временному_файлу
     *      [error] => код_ошибки
     *      [size] => размер_файла
     * )
     * ```
     *
     * Возвращает true, если файл загружен. Возвращает false, если не удалось загрузить файл на диск.
     *
     * Если ресурс с таким именем уже существует, файл загружен не будет.
     *
     * @param array $file Параметры файла
     * @return bool
     */
    public function uploadFile($file)
    {
        $status = false;
        if (!empty($file['name']) && !empty($file['tmp_name']) && isset($file['error']) && !$file['error']) {
            $parentPath = $this->resource->get('path');
            if ($parentPath[-1] === '/') {
                $filePath = $parentPath . trim($file['name']);
            } else {
                $filePath = $parentPath . '/' . trim($file['name']);
            }

            $fileResource = $this->disk->getResource($filePath);
            $operation = $fileResource->upload($file['tmp_name']);

            if (is_bool($operation)) {
                $status = $operation;
            } elseif ($operation instanceof Operation) {
                do {
                    $status = $operation->isSuccess()? true: ($operation->isFailure()? false: $status);
                } while ($operation->isPending());
            }
        }

        return $status;
    }

    /**
     * Удаляет ресурс с яндекс-диска.
     *
     * Возвращает true, если ресурс удален. Возвращает false, если не удалось удалить ресурс с яндекс-диска.
     *
     * @param string $path Путь к удаляемому ресурсу на диске
     * @return bool
     */
    public function deleteResource($path)
    {
        $status = false;
        if (trim($path)) {
            $deleteResource = $this->disk->getResource(trim($path));
            $operation = $deleteResource->delete();

            if (is_bool($operation)) {
                $status = $operation;
            } elseif ($operation instanceof Operation) {
                do {
                    $status = $operation->isSuccess()? true: ($operation->isFailure()? false: $status);
                } while ($operation->isPending());
            }
        }

        return $status;
    }

    /**
     * Изменяет название текущей директории яндекс-диска.
     *
     * Возвращает true, если название было изменено. Возвращает false, если не удалось изменить название.
     *
     * Если ресурс с таким именем уже существует, папка переименована не будет.
     *
     * @param string Новое имя папки
     * @return bool
     */
    public function changeFolderName($name)
    {
        $status = false;
        if ($this->resource->get('name') === trim($name)) {
            $status = true;
        } elseif (trim($name)) {
            $parentPath = $this->getParentPath();
            if ($parentPath[-1] === '/') {
                $newPath = $parentPath . trim($name);
            } else {
                $newPath = $parentPath . '/' . trim($name);
            }

            $newResource = $this->disk->getResource($newPath);
            $operation = $this->resource->move($newResource);

            if (is_bool($operation)) {
                $status = $operation;
            } elseif ($operation instanceof Operation) {
                do {
                    $status = $operation->isSuccess()? true: ($operation->isFailure()? false: $status);
                } while ($operation->isPending());
            }

            if ($status) {
                $this->resource = $newResource;
            }
        }

        return $status;
    }

    /**
     * Формирует HTML-код для иконки директории яндекс-диска.
     *
     * Если директория является родительской для текущей директории, следует передавать значение true
     * для параметра $parent. В этом случае отсутствует всплывающая подсказка, и элемент checkbox формируется
     * без аттрибута "name" (не передается вместе с другими полями формы).
     *
     * @param string $path Путь к папке на диске, для которой формируется иконка
     * @param string $name Имя иконки
     * @param bool $parent Тип папки, для которой формируется иконка (родительская или дочерняя).
     *                     Необязательный параметр
     * @return string HTML-код иконки
     */
    public static function getFolderHtml($path, $name, $parent = false)
    {
        if ($parent) {
            $result = '
            <a href="index.php?path='.htmlspecialchars(urlencode($path)).'" class="resource-item">
                <input type="checkbox" class="checkbox-item">
                <img src="images/folder.svg" alt="">
                <h1>'.htmlspecialchars($name).'</h1>
            </a>';
        } else {
            $result = '
            <a href="index.php?path='.htmlspecialchars(urlencode($path)).'" class="resource-item" title="'
            .htmlspecialchars($name).'">
                <input type="checkbox" name="item_path[]" value="'.htmlspecialchars($path)
                .'" class="checkbox-item">
                <img src="images/folder.svg" alt="">
                <h1>'.htmlspecialchars($name).'</h1>
            </a>';
        }
        return $result;
    }

    /**
     * Формирует HTML-код для иконки файла яндекс-диска.
     *
     * Параметр $type влияет на изображение, размещенное на иконке. Если параметр не передан, на иконке
     * будет изображение по умолчанию.
     *
     * @param string $path Путь к файлу на диске, для которого формируется иконка
     * @param string $name Имя иконки
     * @param string $type MIME-тип файла. Необязательный параметр
     * @return string HTML-код иконки
     */
    public static function getFileHtml($path, $name, $type = '')
    {
        switch (true) {
            case preg_match('/audio\/.+/', $type):
                $icon = 'images/audio.svg';
                break;
            case preg_match('/image\/.+/', $type):
                $icon = 'images/image.svg';
                break;
            case preg_match('/text\/.+/', $type):
                $icon = 'images/text.svg';
                break;
            case preg_match('/video\/.+/', $type):
                $icon = 'images/video.svg';
                break;
            default:
                $icon = 'images/blank.svg';
                break;
        }

        $result = '
            <a href="file.php?path='.htmlspecialchars(urlencode($path)).'" class="resource-item" title="'
            .htmlspecialchars($name).'">
            <input type="checkbox" name="item_path[]" value="'.htmlspecialchars($path)
            .'" class="checkbox-item">
            <img src="'.$icon.'" alt="">
            <h1>'.htmlspecialchars($name).'</h1>
        </a>';
        return $result;
    }
}
