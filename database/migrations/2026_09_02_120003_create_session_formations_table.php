<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_formations', function (Blueprint $table) {
            $table->id();
            $table->string('num_GESCOF')->index();
            $table->string('nom')->comment('Libellé du stage');
            $table->enum('code_produit', ['FPC', 'OP'])->default('OP');
            $table->string('langue')->default('Anglais');

            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('formateur_id')->nullable()->constrained('users')->nullOnDelete();

            // FPC : objectifs pédagogiques personnalisés de la session.
            $table->text('objectifs')->nullable();

            // Formation intégralement à distance : déclenche émargements en ligne,
            // évaluation des acquis, questionnaire à chaud en ligne, lien Teams.
            $table->boolean('distanciel')->default(false);
            $table->string('lien_teams')->nullable();

            // OP : inscription au trimestre ou à l'année.
            $table->enum('rythme_op', ['trimestre', 'annee'])->nullable();

            // Export brut de la liste de dates depuis GESCOF (source de vérité).
            $table->text('dates_planning')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_formations');
    }
};
