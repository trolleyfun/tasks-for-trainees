<?php

namespace Trolleyfun\Yandex;

use Arhitector\Yandex\Disk\Operation;
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
            $result .= self::getFolderHtml($parentPath, '..', true);
        }

        foreach ($this->resource->get('items') as $item) {
            if ($item->isDir()) {
                $result .= self::getFolderHtml($item->get('path'), $item->get('name'));
            } elseif ($item->isFile()) {
                $result .= self::getFileHtml($item->get('path'), $item->get('name'), $item->get('media_type'));
            }
        }

        return $result;
    }

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

    public static function getFileHtml($path, $name, $type = '')
    {
        switch ($type) {
            case 'audio':
                $icon = 'images/audio.svg';
                break;
            case 'image':
                $icon = 'images/image.svg';
                break;
            case 'text':
                $icon = 'images/text.svg';
                break;
            case 'video':
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
