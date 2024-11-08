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

    public function getParentPath()
    {
        $arPath = explode('/', $this->resource->get('path'));
        while (!array_pop($arPath) && count($arPath) > 0) {
            continue;
        }
        array_push($arPath, '');
        return implode('/', $arPath);
    }

    public static function getFolderHtml($path, $name)
    {
        $result = '
        <a href="index.php?dir='.htmlspecialchars(urlencode($path)).'" class="resource-item" title="'
        .htmlspecialchars($name).'">
            <input type="checkbox" name="item_path[]" value="'.htmlspecialchars($path)
            .'" class="checkbox-item">
            <img src="images/folder.svg" alt="">
            <h1>'.htmlspecialchars($name).'</h1>
        </a>';
        return $result;
    }

    public static function getFileHtml($path, $name)
    {
        $result = '
        <div class="resource-item" title="'.htmlspecialchars($name).'">
            <input type="checkbox" name="item_path[]" value="'.htmlspecialchars($path)
            .'" class="checkbox-item">
            <img src="images/image.svg" alt="">
            <h1>'.htmlspecialchars($name).'</h1>
        </div>';
        return $result;
    }
}
