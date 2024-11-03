<?php

namespace Test\Complexprop\SubProperties;

/**
 * Базовый класс для типов свойств комплексного свойства.
 */
abstract class BaseType
{
    /**
     * @var string $code Код свойства
     */
    protected $code;

    /**
     * @var string $name Название свойства
     */
    protected $name;

    /**
     * @var string $typeCode Код типа свойства
     */
    protected $typeCode;

    public function __construct($code, $name, $typeCode)
    {
        $this->code = $code;
        $this->name = $name;
        $this->typeCode = $typeCode;
    }

    /**
     * Возвращает код свойства.
     *
     * @return string Код свойства
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Возвращает название свойства.
     *
     * @return string Название свойства
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Возвращает код типа свойства.
     *
     * @return string Код типа свойства
     */
    public function getTypeCode()
    {
        return $this->typeCode;
    }

    /**
     * Возвращает название типа свойства.
     *
     * @return string Название типа свойства
     */
    abstract public static function getTypeName(): string;

    /**
     * Формирует HTML-код для формы редактирования свойства в административном разделе.
     *
     * @param mixed $value Значение свойства
     * @param string $name Значение аттрибута "name" полей формы
     * @return string HTML-код формы редактирования свойства
     */
    abstract public function getPropertyFieldHtml($value, string $name): string;

    /**
     * Преобразовывает значение свойства перед сохранением в базу данных.
     *
     * @param mixed $value Значение свойства
     * @return mixed Преобразованное значение свойства
     */
    public function onBeforeSave($value): mixed
    {
        return $value;
    }

    /**
     * Преобразовывает значение свойства после извлечения из базы данных.
     *
     * @param mixed $value Значение свойства
     * @return mixed Преобразованное значение свойства
     */
    public function onAfterReceive($value): mixed
    {
        return $value;
    }

    /**
     * Проверяет, заполнено ли свойство.
     *
     * Возвращает true, если свойство заполнено. Возвращает false, если свойство не заполнено.
     *
     * @param mixed $value Значение свойства.
     * @return bool
     */
    public function getLength($value): bool
    {
        return true;
    }

    /**
     * Проверяет корректность введенных пользователем данных.
     *
     * Возвращает массив с текстом ошибок. Если введенные данные корректны, вернет пустой массив.
     *
     * @param mixed $value Значение свойства
     * @return bool
     */
    public function checkFields($value): array
    {
        return array();
    }

    /**
     * Проверяет, является ли значение свойства пустым значением.
     *
     * Возвращает true, если значение свойства пустое. Возвращает false, если значение свойства не пустое.
     *
     * @param mixed $value Значение свойства.
     * @return bool
     */
    public function isEmpty($value): bool
    {
        return empty($value);
    }
}
