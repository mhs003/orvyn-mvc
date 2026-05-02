<?php

use Core\LunaORM\Migration;
use Core\LunaORM\Schema;
use Core\LunaORM\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('users');
    }
};