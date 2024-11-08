<?php

namespace Trolleyfun\Yandex;

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
}
