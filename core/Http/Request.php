<?php

namespace Core\Http;

class Request
{
    public function __construct(
        public array $get,
        public array $post,
        public array $server
    ) {}

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }

    public function method(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'];
    }

    public function input($key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }
}