<?php

namespace Core\ErrorHandlers;

class DebugHelper
{
    /**
     * Dump and die
     * 
     * @param array $trace Debug backtrace
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    public static function dd($trace, ...$vars)
    {
        // Prevent any output buffering issues
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Ensure clean output
        header('Content-Type: text/html; charset=utf-8');
        header('X-Debug-Mode: Active');

        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Debug Dump</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
            <style>
                body {
                    background-color: #f0f0f0;
                    margin: 0;
                    padding: 0;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    color: #212529;
                    display: flex;
                }
                .exception-container {
                    width: 100%;
                    background: #fff;
                    padding: 30px;
                    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
                    border-top: 4px solid #e74c3c;
                }
                .exception-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                }
                .exception-title {
                    font-size: 24px;
                    color: #e74c3c;
                    margin: 0;
                }
                .source-info {
                    background-color: #f8f9fa;
                    border-left: 4px solid #007bff;
                    padding: 15px;
                    margin: 20px 0;
                }
                .debug-var {
                    background-color: #f1f3f5;
                    border-radius: 4px;
                    padding: 15px;
                    margin-bottom: 20px;
                    overflow-x: auto;
                }
                pre {
                    white-space: pre-wrap;
                    word-wrap: break-word;
                    font-family: "Fira Code", Consolas, monospace;
                    font-size: 14px;
                    line-height: 1.6;
                    margin: 0;
                }
                .syntax-highlight {
                    background-color: #f4f4f4;
                    border: 1px solid #e0e0e0;
                    border-radius: 4px;
                    padding: 15px;
                }
            </style>
        </head>
        <body>
            <div class="exception-container">
                <div class="exception-header">
                    <h1 class="exception-title">Debug Dump</h1>
                </div>
                
                <div class="source-info">
                    <strong>Called from:</strong> ' . htmlspecialchars($trace[1]['file'] ?? $trace[0]['file']) . ' 
                    on line ' . htmlspecialchars($trace[1]['line'] ?? $trace[0]['line']) . '
                </div>';

        foreach ($vars as $index => $var) {
            echo '<div class="debug-var">';
            echo '<h3>Variable ' . ($index + 1) . ' Details</h3>';
            echo '<div class="syntax-highlight">';

            // Capture output with output control
            ob_start();
            var_dump($var);
            $dumpOutput = ob_get_clean();

            echo '<pre><code class="language-php">' . htmlspecialchars($dumpOutput) . '</code></pre>';
            echo '</div></div>';
        }

        echo '<script>hljs.highlightAll();</script>
        </div>
        </body>
        </html>';

        exit(1);
    }

    /**
     * Soft dump
     * 
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    public static function dump(...$vars)
    {
        echo '<div style="background-color:#f4f4f4;padding:15px;margin:10px;border-radius:4px;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</div>';
    }

    /**
     * Logging with more context
     * 
     * @param mixed $var Variable to log
     * @param string $logFile Path to log file
     * @return void
     */
    public static function log($trace, $var, $logFile)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $caller = $trace[1] ?? $trace[0];

        $logEntry = sprintf(
            "[%s] Called from %s on line %d\n%s\n---\n",
            date('Y-m-d H:i:s'),
            $caller['file'],
            $caller['line'],
            print_r($var, true)
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
