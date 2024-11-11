<?php

namespace Trolleyfun\Yandex;

use Arhitector\Yandex\Disk\Operation;
use Trolleyfun\Yandex\Exception\FileCreationFailureException;
use Trolleyfun\Yandex\Exception\ResourceTypeNotValidException;

/**
 * Класс для управлениями файлами на яндекс-диске.
 */
class FileManager extends DiskManager
{
    /**
     * @param string $oauthToken
     * @param string $resourcePath
     * @throws Trolleyfun\Yandex\Exception\ResourceTypeNotValidException
     *      если ресурс не является файлом.
     * @return void
     */
    public function __construct($oauthToken, $resourcePath)
    {
        parent::__construct($oauthToken, $resourcePath);
        if (!$this->resource->isFile()) {
            throw new ResourceTypeNotValidException('Ресурс не является файлом');
        }
    }

    /**
     * Возвращает ссылку на загрузку текущего файла с диска.
     *
     * @return string
     */
    public function getDownloadLink()
    {
        return $this->resource->get('file');
    }

    /**
     * Возвращает MIME-тип текущего файла.
     *
     * @return string
     */
    public function getFileType()
    {
        return $this->resource->get('mime_type');
    }

    /**
     * Проверяет, является ли текущий файл текстовым.
     *
     * Возвращает true, если MIME-тип файла "text/*". В противном случае возвращает false.
     *
     * @return bool
     */
    public function isText()
    {
        return (bool)preg_match('/text\/.+/', $this->resource->get('mime_type'));
    }

    /**
     * Возвращает содержимое текущего файла.
     *
     * Возвращает содержимое файла в виде строки, если файл текстовый. Если файл не текстовый, возвращает
     * пустую строку.
     *
     * @throws Trolleyfun\Yandex\Exception\FileCreationFailureException
     *      если не удалось создать временный файл.
     * @return string Содержимое файла
     */
    public function getTextFileContent()
    {
        $content = '';
        if ($this->isText()) {
            $fp = tmpfile();
            if (!$fp) {
                throw new FileCreationFailureException('Не удалось создать временный файл');
            } else {
                $this->resource->download($fp);
                rewind($fp);
                $content = stream_get_contents($fp);
                fclose($fp);
            }
        }
        return (string)$content;
    }

    /**
     * Сохраняет текущий файл.
     *
     * Для текстовых файлов сохраняет название и содержимое файла. Для остальных типов файлов сохраняет
     * название файла.
     *
     * Возвращает true, если изменения в файле сохранены. Возвращает false, если не удалось сохранить файл.
     *
     * Если ресурс с таким именем уже существует, изменения в файле сохранены не будут.
     *
     * @param string $name Новое имя файла
     * @param string $content Новое содержимое файла (для текстовых файлов). Необязательный параметр
     * @return bool
     */
    public function updateFile($name, $content = '')
    {
        if ($this->isText()) {
            return $this->updateTextFile($name, $content);
        } else {
            return $this->updateNotTextFile($name);
        }
    }

    /**
     * Сохраняет файл, не являющийся текстовым.
     *
     * Сохраняет новое название файла.
     *
     * Возвращает true, если изменения в файле сохранены. Возвращает false, если не удалось сохранить файл.
     *
     * Если текущий файл является текстовым, сохранится только его название. Изменения в содержимом
     * сохранены не будут. При этом метод вернёт значение true.
     *
     * Если ресурс с таким именем уже существует, изменения в файле сохранены не будут.
     *
     * @param string $name Новое имя файла
     * @return bool
     */
    public function updateNotTextFile($name)
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
     * Сохраняет файл, являющийся текстовым.
     *
     * Сохраняет новое название и содержимое текущего файла.
     *
     * Возвращает true, если изменения в файле сохранены. Возвращает false, если не удалось сохранить файл.
     *
     * Если файл не является текстовым, изменения сохранены не будут, метод вернёт значение false.
     *
     * Если ресурс с таким именем уже существует, изменения в файле сохранены не будут.
     *
     * @param string $name Новое имя файла
     * @param string $content Новое содержимое файла
     * @throws Trolleyfun\Yandex\Exception\FileCreationFailureException
     *      если не удалось создать временный файл.
     * @return bool
     */
    public function updateTextFile($name, $content)
    {
        $status = false;
        if ($this->isText()) {
            $fp = tmpfile();
            if (!$fp) {
                throw new FileCreationFailureException('Не удалось создать временный файл');
            } else {
                fwrite($fp, $content);
                rewind($fp);

                if ($this->resource->get('name') === trim($name)) {
                    $operation = $this->resource->upload($fp, true);

                    if (is_bool($operation)) {
                        $status = $operation;
                    } elseif ($operation instanceof Operation) {
                        do {
                            $status = $operation->isSuccess()? true: ($operation->isFailure()? false: $status);
                        } while ($operation->isPending());
                    }
                } elseif (trim($name)) {
                    $parentPath = $this->getParentPath();
                    if ($parentPath[-1] === '/') {
                        $newPath = $parentPath . trim($name);
                    } else {
                        $newPath = $parentPath . '/' . trim($name);
                    }

                    $newResource = $this->disk->getResource($newPath);
                    $operation = $newResource->upload($fp);

                    if (is_bool($operation)) {
                        $status = $operation;
                    } elseif ($operation instanceof Operation) {
                        do {
                            $status = $operation->isSuccess()? true: ($operation->isFailure()? false: $status);
                        } while ($operation->isPending());
                    }

                    if ($status) {
                        $delete = $this->resource->delete(true);
                        while ($delete instanceof Operation && $delete->isPending()) {
                            continue;
                        }
                        $this->resource = $newResource;
                    }
                }

                fclose($fp);
            }
        }

        return $status;
    }
}
