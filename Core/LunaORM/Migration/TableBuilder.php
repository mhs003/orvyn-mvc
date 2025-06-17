<?php

namespace Core\LunaORM\Migration;

class TableBuilder
{
    protected array $columns;

    public function __construct(array &$columns)
    {
        $this->columns = &$columns;
    }

    public function id(string $name = 'id'): void
    {
        if(config('database.usedriver') === 'sqlite') {
            $this->columns[] = "$name INTEGER PRIMARY KEY AUTOINCREMENT";
        } else if(config('database.usedriver') === 'mysql') {
            $this->columns[] = "$name INT AUTO_INCREMENT PRIMARY KEY";
        }
    }

    public function string(string $name, int $length = 255): void
    {
        $this->columns[] = "$name VARCHAR($length)";
    }

    public function text(string $name): void
    {
        $this->columns[] = "$name TEXT";
    }

    public function integer(string $name): void
    {
        if(config('database.usedriver') === 'sqlite') {
            $this->columns[] = "$name INTEGER";
        } else if(config('database.usedriver') === 'mysql') {
            $this->columns[] = "$name INT";
        }
    }

    public function boolean(string $name): void
    {
        if(config('database.usedriver') === 'sqlite') {
            $this->columns[] = "$name BOOLEAN";
        } else if(config('database.usedriver') === 'mysql') {
            $this->columns[] = "$name TINYINT(1)";
        }
    }

    public function timestamp(string $name): void
    {
        if(config('database.usedriver') === 'sqlite') {
            $this->columns[] = "$name DATETIME";
        } else if(config('database.usedriver') === 'mysql') {
            $this->columns[] = "$name TIMESTAMP";
        }
    }
}
