<?php

namespace Yandex;

use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;

require_once('vendor/autoload.php');

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

    public function displayItems()
    {
        $result = '';

        if ($parentPath = $this->getParentPath()) {
            $result .= self::getFolderHtml(urlencode($parentPath), '..');
        }

        foreach ($this->resource->get('items') as $item) {
            if ($item->isDir()) {
                $result .= self::getFolderHtml(urlencode($item->get('path')), $item->get('name'));
            } elseif ($item->isFile()) {
                $result .= self::getFileHtml(urlencode($item->get('path')), $item->get('name'));
            }
        }

        return $result;
    }

    public function createFolder($name)
    {
        $parentPath = $this->resource->get('path');
        if ($parentPath[-1] === '/') {
            $newPath = $parentPath . trim($name);
        } else {
            $newPath = $parentPath . '/' . trim($name);
        }

        $newResource = $this->disk->getResource($newPath);
        $newResource->create();
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }

    public function uploadFile($file)
    {
        if (!empty($file['name']) && !empty($file['tmp_name']) && isset($file['error']) && !$file['error']) {
            $parentPath = $this->resource->get('path');
            if ($parentPath[-1] === '/') {
                $filePath = $parentPath . trim($file['name']);
            } else {
                $filePath = $parentPath . '/' . trim($file['name']);
            }

            $fileResource = $this->disk->getResource($filePath);
            $fileResource->upload($file['tmp_name']);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }

    public static function getFolderHtml($path, $name)
    {
        $result = '
        <a href="index.php?dir='.urlencode($path).'" class="resource-item" title="'.$name.'">
            <input type="checkbox">
            <img src="images/folder.svg" alt="">
            <h1>'.$name.'</h1>
        </a>';
        return $result;
    }

    public static function getFileHtml($path, $name)
    {
        $result = '
        <div class="resource-item" title="'.$name.'">
            <input type="checkbox">
            <img src="images/image.svg" alt="">
            <h1>'.$name.'</h1>
        </div>';
        return $result;
    }
}
