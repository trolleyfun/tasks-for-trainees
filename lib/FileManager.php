<?php

namespace Trolleyfun\Yandex;

use Arhitector\Yandex\Disk\Operation;
use Trolleyfun\Yandex\Exception\FileCreationFailureException;
use Trolleyfun\Yandex\Exception\ResourceTypeNotValidException;

class FileManager extends DiskManager
{
    public function __construct($oauthToken, $resourcePath)
    {
        parent::__construct($oauthToken, $resourcePath);
        if (!$this->resource->isFile()) {
            throw new ResourceTypeNotValidException('Ресурс не является файлом');
        }
    }

    public function getDownloadLink()
    {
        return $this->resource->get('file');
    }

    public function getFileType()
    {
        return $this->resource->get('mime_type');
    }

    public function isText()
    {
        return (bool)preg_match('/text\/.+/', $this->resource->get('mime_type'));
    }

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
        return $content;
    }

    public function updateFile($name, $content)
    {
        if ($this->isText()) {
            return $this->updateTextFile($name, $content);
        } else {
            return $this->updateNotTextFile($name);
        }
    }

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
