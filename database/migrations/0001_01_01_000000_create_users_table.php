<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Une même adresse e-mail peut porter plusieurs comptes (1 accès = 1 session).
            $table->string('email')->index();
            $table->string('login')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'formateur', 'stagiaire_op', 'stagiaire_fpc'])
                ->default('stagiaire_op');
            $table->string('nom');
            $table->string('prenom');

            // Informations formateur (affichées sur l'espace des stagiaires).
            $table->string('photo_path')->nullable();
            $table->text('presentation')->nullable();
            $table->boolean('formateur_fpc')->default(false);
            $table->boolean('formateur_op')->default(false);

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Jetons de création / réinitialisation de mot de passe (schéma maison).
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->dateTime('expiration')->index();
            $table->boolean('used')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
