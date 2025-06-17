<?php

namespace Core\LunaORM\Migration;

abstract class BaseMigration
{
    abstract public function up(SchemaBuilder $schema): void;

    public function down(SchemaBuilder $schema): void
    {
        // Optional to implement
    }
}
