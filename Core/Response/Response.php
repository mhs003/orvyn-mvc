<?php

namespace Core\Response;

/**
 * Response Class
 * 
 * Handles HTTP response creation and sending
 * 
 * @package Core\Response
 */
class Response
{
    /**
     * Response content
     * 
     * @var string
     */
    protected static $content = '';

    /**
     * HTTP status code
     * 
     * @var int
     */
    protected static $statusCode = 200;

    /**
     * Response headers
     * 
     * @var array<string, string>
     */
    protected static $headers = [];

    /**
     * Set the HTTP status code
     * 
     * @param int $code HTTP status code
     * @return static
     */
    public static function setStatusCode($code)
    {
        self::$statusCode = $code;
        return new static();
    }

    /**
     * Add a header to the response
     * 
     * @param string $name The header name
     * @param string $value The header value 
     * @return static
     */
    public static function header($name, $value)
    {
        self::$headers[$name] = $value;
        return new static();
    }

    /**
     * Set the response content
     * 
     * @param string $content The content to send in the response
     * @param int $statusCode Optional HTTP status code
     * @return static
     */
    public static function setContent($content, $statusCode = 200)
    {
        self::$content = $content;
        self::setStatusCode($statusCode);
        return new static();
    }

    /**
     * Send a JSON response
     * 
     * @param mixed $data The data to be encoded as JSON
     * @param int $statusCode Optional HTTP status code
     * @return static
     */
    public static function json($data, $statusCode = 200)
    {
        self::setContent(json_encode($data));
        self::header('Content-Type', 'application/json');
        self::setStatusCode($statusCode);
        return new static();
    }

    /**
     * Send a file download response
     * 
     * @param string $filePath Path to the file to be downloaded
     * @param string|null $fileName Optional custom name for the downloaded file
     * @return void
     */
    public static function download($filePath, $fileName = null)
    {
        if (file_exists($filePath)) {
            $fileName = $fileName ?? basename($filePath);
            self::header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->header('Content-Length', filesize($filePath));
            readfile($filePath);
        } else {
            self::setStatusCode(404)->setContent('File not found.');
        }
        return self::send();
    }

    /**
     * Redirect to another URL
     * 
     * @param string $url The URL to redirect to
     * @param int $statusCode HTTP status code for the redirect (default: 302)
     * @return static
     */
    public static function redirect($url, $statusCode = 302)
    {
        self::setStatusCode($statusCode);
        self::header('Location', $url);
        return new static();
    }

    /**
     * Send the response to the client
     * 
     * Sets the HTTP status code, sends all headers,
     * and outputs the response content
     * 
     * @return void
     */
    public static function send()
    {
        http_response_code(self::$statusCode);

        // Send headers
        foreach (self::$headers as $name => $value) {
            header("$name: $value");
        }

        // Send content
        echo self::$content;
    }
}
