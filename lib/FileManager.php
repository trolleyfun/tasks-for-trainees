<?php

namespace Trolleyfun\Yandex;

use Trolleyfun\Yandex\Exception\FileCreationFailure;
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
                throw new FileCreationFailure('Не удалось создать временный файл');
            } else {
                $this->resource->download($fp);
                rewind($fp);
                $content = stream_get_contents($fp);
                fclose($fp);
            }
        }
        return $content;
    }
}
