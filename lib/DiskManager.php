<?php

namespace Trolleyfun\Yandex;

use Arhitector\Yandex\Disk;

/**
 * Базовый класс для управления ресурсами яндекс-диска.
 */
class DiskManager
{
    /**
     * @var Arhitector\Yandex\Disk $disk
     */
    protected $disk;

    /**
     * @var Arhitector\Yandex\Disk\Resource\Closed $resource
     */
    protected $resource;

    /**
     * @param string $oauthToken
     * @param string $resourcePath
     * @return void
     */
    public function __construct($oauthToken, $resourcePath)
    {
        $this->disk = new Disk($oauthToken);
        $this->resource = $this->disk->getResource($resourcePath);
    }

    /**
     * Возвращает название ресурса.
     *
     * @return string
     */
    public function getName()
    {
        return $this->resource->get('name');
    }

    /**
     * Возвращает путь к ресурсу на диске.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->resource->get('path');
    }

    /**
     * Возвращает путь к родительскому каталогу ресурса на диске.
     *
     * @return string
     */
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
