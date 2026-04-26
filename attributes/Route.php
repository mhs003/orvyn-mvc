<?php

namespace Attributes;

#[\Attribute]
class Route
{
    public function __construct(public string $path) {}
}