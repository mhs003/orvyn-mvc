<?php

use Core\LunaORM\Migration\BaseMigration;
use Core\LunaORM\Migration\SchemaBuilder;

return new class extends BaseMigration
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->create('books', function ($table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->integer('rating');
            $table->boolean('is_published');
            $table->timestamp('created_at');
        });
    }
    public function down(SchemaBuilder $schema): void
    {
        $schema->dropIfExists('books');
    }
};