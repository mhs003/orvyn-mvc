<?php

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Console\Input;

class ServeCommand extends Command
{
    public string $signature = 'serve';
    public string $description = 'Start the orvyn server';

    public function handle(Input $input): void
    {
        $inp = $input->arg(0);
        [$host, $port] = $this->extract_host_port($inp ?? '127.0.0.1:1078');
        $host = $host ?: '127.0.0.1';
        $port = $port ?: 1078;
        
        $docRoot = getcwd() . '/public';
        $command = \sprintf(
            'php -S %s:%d -t %s',
            escapeshellarg($host),
            $port,
            escapeshellarg($docRoot)
        );

        $this->success("Orvyn server started:");
        $this->bold("→ http://{$host}:{$port}");
        $this->dim("Press Ctrl+C to stop\n");

        passthru($command);
    }

    private function extract_host_port(string $addr): array
    {
        $host = $addr;
        $port = null;

        if (str_starts_with($addr, '[')) {
            $end = strpos($addr, ']');
            if ($end !== false) {
                $host = substr($addr, 1, $end - 1);
                $portPart = substr($addr, $end + 1);

                if (str_starts_with($portPart, ':')) {
                    $port = (int) substr($portPart, 1);
                }
            }
            return [$host, $port];
        }

        $parts = explode(':', $addr);

        if (\count($parts) === 2) {
            [$host, $port] = $parts;
            $port = is_numeric($port) ? (int) $port : null;
        }

        return [$host, $port];
    }
}