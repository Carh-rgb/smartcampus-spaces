<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega a la tabla base de Laravel los campos de la entidad "usuarios"
     * definida en la Actividad 1 (rol dentro del campus y estado de la cuenta).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // MER Actividad 1: usuarios.rol -> admin | teacher | student
            $table->enum('role', ['admin', 'teacher', 'student'])
                ->default('student')
                ->after('password');

            $table->boolean('is_active')->default(true)->after('role');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
