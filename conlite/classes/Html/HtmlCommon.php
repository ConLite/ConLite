<?php

namespace ConLite\Html;

abstract class HtmlCommon implements \ArrayAccess
{

    protected string|array|null $attributes = null;

    public function __construct(array|string|null $attributes = null)
    {
        $this->attributes = $attributes;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[strtolower($offset)]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        return $this->getAttribute($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        if (null !== $offset) {
            $this->setAttribute($offset, $value);
        } else {
            $this->setAttribute($value);
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        $this->removeAttribute($offset);
    }
}