<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inscription d'un utilisateur à une session. Pour un stagiaire, l'accès est
     * porté par cette inscription (1 accès = 1 session). num_GESCOF conserve la
     * ligne d'import d'origine ; disparu_import repère les inscriptions absentes
     * du dernier import sans pour autant les supprimer.
     */
    public function up(): void
    {
        Schema::create('session_formation_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_formation_id')->constrained('session_formations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('disparu_import_at')->nullable();
            $table->timestamps();

            $table->unique(['session_formation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_formation_user');
    }
};
