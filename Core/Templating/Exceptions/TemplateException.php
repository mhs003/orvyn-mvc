<?php
namespace Core\Templating\Exceptions;

use Exception;

class TemplateException extends Exception {
    protected $template;
    protected $lineNumber;

    public function __construct(string $message, string $template = null, int $lineNumber = null) {
        $this->template = $template;
        $this->lineNumber = $lineNumber;

        if ($template && $lineNumber) {
            $message .= " in {$template} on line {$lineNumber}.";
        }

        parent::__construct($message);
    }

    public function getTemplate(): ?string {
        return $this->template;
    }

    public function getLineNumber(): ?int {
        return $this->lineNumber;
    }
}