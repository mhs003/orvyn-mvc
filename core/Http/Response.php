<?php

namespace Core\Http;

class Response
{
    protected array $headers = [];

    public function __construct(
        public mixed $content,
        public int $status = 200
    ) {}

    public static function make($content, $status = 200)
    {
        return new self($content, $status);
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send()
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        echo $this->content;
    }


    public function json(array $data, int $status = 200): self
    {
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->status = $status;

        return $this->header('Content-Type', 'application/json');
    }

    public static function jsonResponse(array $data, int $status = 200): self
    {
        return (new self('', $status))->json($data, $status);
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return (new self('', $statusCode))->header('Location', $url);
    }
}