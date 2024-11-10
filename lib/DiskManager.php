<?php

namespace Trolleyfun\Yandex;

use Arhitector\Yandex\Disk;

class DiskManager
{
    protected $disk;

    protected $resource;

    public function __construct($oauthToken, $resourcePath)
    {
        $this->disk = new Disk($oauthToken);
        $this->resource = $this->disk->getResource($resourcePath);
    }

    public function getName()
    {
        return $this->resource->get('name');
    }

    public function getPath()
    {
        return $this->resource->get('path');
    }

    public function getParentPath()
    {
        $arPath = explode('/', $this->resource->get('path'));
        while (!array_pop($arPath) && count($arPath) > 0) {
            continue;
        }
        array_push($arPath, '');
        return implode('/', $arPath);
    }
}
