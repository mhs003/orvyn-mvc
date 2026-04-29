<?php

use Core\LunaORM\Migration;
use Core\LunaORM\Schema;
use Core\LunaORM\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test', function (Blueprint $table) {
            //
        });
    }

    public function down(): void
    {
        Schema::table('test', function (Blueprint $table) {
            //
        });
    }
};