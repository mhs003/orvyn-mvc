<?php

namespace Attributes;

#[\Attribute]
class Method
{
    public function __construct(public string $method) {}
}