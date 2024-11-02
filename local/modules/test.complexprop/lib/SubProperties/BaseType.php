<?php

namespace Test\Complexprop\SubProperties;

abstract class BaseType
{
    protected $code;

    protected $name;

    protected $typeCode;

    public function __construct($code, $name, $typeCode)
    {
        $this->code = $code;
        $this->name = $name;
        $this->typeCode = $typeCode;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTypeCode()
    {
        return $this->typeCode;
    }

    abstract public static function getTypeName(): string;

    abstract public function getPropertyFieldHtml($value, string $name): string;

    public function onBeforeSave($value): mixed
    {
        return $value;
    }

    public function onAfterReceive($value): mixed
    {
        return $value;
    }

    public function getLength($value): bool
    {
        return true;
    }

    public function checkFields($value): array
    {
        return array();
    }

    public function isEmpty($value): bool
    {
        return empty($value);
    }
}
