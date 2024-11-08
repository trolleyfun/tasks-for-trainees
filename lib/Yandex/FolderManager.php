<?php

namespace Trolleyfun\Yandex;

use Trolleyfun\Yandex\Exception\ResourceTypeNotValidException;

class FolderManager extends DiskManager
{
    public function __construct($oauthToken, $resourcePath)
    {
        parent::__construct($oauthToken, $resourcePath);
        if (!$this->resource->isDir()) {
            throw new ResourceTypeNotValidException('Ресурс не является папкой');
        }
    }

    public function displayItems()
    {
        $result = '';

        if ($parentPath = $this->getParentPath()) {
            $result .= self::getFolderHtml($parentPath, '..');
        }

        foreach ($this->resource->get('items') as $item) {
            if ($item->isDir()) {
                $result .= self::getFolderHtml($item->get('path'), $item->get('name'));
            } elseif ($item->isFile()) {
                $result .= self::getFileHtml($item->get('path'), $item->get('name'));
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
}
