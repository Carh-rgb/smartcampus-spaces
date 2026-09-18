<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crea la tabla 'users' para almacenar a los usuarios del sistema
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('name'); // Nombre del usuario
            $table->string('email')->unique(); // Correo electrónico, debe ser único
            $table->timestamp('email_verified_at')->nullable(); // Fecha de verificación de correo
            $table->string('password'); // Contraseña encriptada
            $table->rememberToken(); // Token de "recuérdame" para mantener la sesión
            $table->timestamps(); // Columnas created_at y updated_at
        });

        // Crea la tabla 'password_reset_tokens' para gestionar la recuperación de contraseñas
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // El correo actúa como clave primaria
            $table->string('token'); // Token único para la recuperación
            $table->timestamp('created_at')->nullable(); // Fecha de generación del token
        });

        // Crea la tabla 'sessions' para almacenar las sesiones de los usuarios en base de datos
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // Identificador único de la sesión
            $table->foreignId('user_id')->nullable()->index(); // ID del usuario asociado a la sesión (si existe)
            $table->string('ip_address', 45)->nullable(); // Dirección IP del usuario
            $table->text('user_agent')->nullable(); // Información del navegador y dispositivo
            $table->longText('payload'); // Datos de la sesión serializados
            $table->integer('last_activity')->index(); // Marca de tiempo de la última actividad
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revierte la migración eliminando las tres tablas de la base de datos
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
