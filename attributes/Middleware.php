<?php

namespace Attributes;

#[\Attribute]
class Middleware
{
    public function __construct(public string $class) {}
}