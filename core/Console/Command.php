<?php

namespace Core\Console;


abstract class Command
{
    public string $signature;
    public string $description = '';

    abstract public function handle(array $args): void;


    protected function info(string $text, ?bool $label = false): void
    {
        echo $label ? "\n\e[1;42m  INFO  \e[0m {$text}\n\n" : "\e[0;32m{$text}\e[0m\n";
    }

    protected function error(string $text, ?bool $label = false): void
    {
        echo $label ? "\n\e[1;41m  ERROR  \e[0m {$text}\n\n" : "\e[0;31m{$text}\e[0m\n";
    }
    protected function success(string $text, ?bool $label = false): void
    {
        echo $label ? "\n\e[1;42m SUCCESS \e[0m {$text}\n\n" : "\e[1;32m{$text}\e[0m\n";
    }

    protected function warning(string $text, ?bool $label = false): void
    {
        echo $label ? "\n\e[1;43m WARNING \e[0m {$text}\n\n" : "\e[1;33m{$text}\e[0m\n";
    }

    protected function comment(string $text, ?bool $label = false): void
    {
        echo $label ? "\n\e[1;44m COMMENT \e[0m \n\n" : "\e[0;34m{$text}\e[0m\n";
    }

    protected function mute(string $text, ?bool $label = false): void
    {
        echo $label ? "\n\e[1;44m COMMENT \e[0m {$text}\n\n" : "\e[0;30m{$text}\e[0m\n";
    }

    protected function line(string $text): void
    {
        echo "{$text}\n";
    }

    protected function bold(string $text): void
    {
        echo "\e[1m{$text}\e[0m\n";
    }

    protected function dim(string $text): void
    {
        echo "\e[2m{$text}\e[0m\n";
    }

    protected function highlight(string $text): void
    {
        echo "\e[1;30;43m{$text}\e[0m\n"; // black text on yellow bg
    }

    protected function ask(string $question): string
    {
        echo "\e[1;36m{$question}\e[0m ";
        return trim(fgets(STDIN));
    }

    protected function confirm(string $question, bool $default = false): bool
    {
        $suffix = $default ? ' (Y/n)' : ' (y/N)';
        $answer = strtolower($this->ask($question . $suffix));

        if ($answer === '') {
            return $default;
        }

        return \in_array($answer, ['y', 'yes']);
    }
}