<?php

namespace Core\ErrorHandlers;

class HttpError
{
    /**
     * Render the error page
     * 
     * @param int $code HTTP error status code
     * @return void
     */
    public static function render($code = 500, $message = null)
    {
        // Prevent any output buffering issues
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Ensure clean output
        header('Content-Type: text/html; charset=utf-8');
        header('X-Debug-Mode: Active');

        $errors = [
            400 => 'Bad Request',
            401 => 'Unauthorized Access',
            403 => 'Forbidden Access',
            404 => 'Page not found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error'
        ];

        if ($message) {
            echo self::generateErrorPage($code, $message);
            exit;
        }
        if (!array_key_exists($code, $errors)) {
            $code = 500;
        }

        http_response_code($code);

        echo self::generateErrorPage($code, $errors[$code]);
        exit;
    }

    /**
     * Fenerate the HTML error page
     * 
     * @param int $code HTTP error status code
     * @param string $message Error message
     * @return string Rendered HTML error page
     */
    private static function generateErrorPage($code, $message)
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{$code} | {$message}</title>

    <style media="all">
        * {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }

        body {
            padding: 0;
            margin: 0;
        }

        #notfound {
            position: relative;
            height: 100vh;
            background: #030005;
        }

        #notfound .notfound {
            position: absolute;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }

        .notfound {
            max-width: 800px;
            width: 100%;
            line-height: 1.4;
            text-align: center;
        }

        .notfound .notfound-message {
            position: relative;
            height: 180px;
            margin-bottom: 20px;
            z-index: -1;
        }

        .notfound .notfound-message h1 {
            font-family: 'Montserrat', sans-serif;
            position: absolute;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            font-size: 224px;
            font-weight: 900;
            margin-top: 0px;
            margin-bottom: 0px;
            margin-left: -12px;
            color: #030005;
            text-transform: uppercase;
            text-shadow: -1px -1px 0px #8400ff, 1px 1px 0px #ff005a;
            letter-spacing: -20px;
        }


        .notfound .notfound-message h2 {
            font-family: 'Montserrat', sans-serif;
            position: absolute;
            left: 0;
            right: 0;
            top: 110px;
            font-size: 42px;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            text-shadow: 0px 2px 0px #8400ff;
            letter-spacing: 13px;
            margin: 0;
        }

        .notfound a {
            font-family: 'Montserrat', sans-serif;
            display: inline-block;
            text-transform: uppercase;
            color: #ff005a;
            text-decoration: none;
            border: 2px solid;
            background: transparent;
            padding: 10px 40px;
            font-size: 14px;
            font-weight: 700;
            -webkit-transition: 0.2s all;
            transition: 0.2s all;
        }

        .notfound a:hover {
            color: #8400ff;
        }

        @media only screen and (max-width: 767px) {
            .notfound .notfound-message h2 {
                font-size: 24px;
            }
        }

        @media only screen and (max-width: 480px) {
            .notfound .notfound-message h1 {
                font-size: 182px;
            }
        }
    </style>

    <meta name="robots" content="noindex, follow">
</head>

<body>

    <div id="notfound">
        <div class="notfound">
            <div class="notfound-message">
                <h1>{$code}</h1>
                <h2>{$message}</h2>
            </div>
            <a href="javascript:history.back()">Go Back</a>
        </div>
    </div>
</body>

</html>
HTML;
    }
}