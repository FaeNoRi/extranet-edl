<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index sur les colonnes de filtre/tri non couvertes par une clé étrangère ou
 * un index unique (celles-ci sont déjà indexées).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->index(['role', 'deleted_at'], 'users_role_deleted_at_index'));
        Schema::table('session_formations', fn (Blueprint $t) => $t->index('code_produit', 'session_formations_code_produit_index'));
        Schema::table('seances', fn (Blueprint $t) => $t->index('date', 'seances_date_index'));
        Schema::table('ressources', fn (Blueprint $t) => $t->index('created_at', 'ressources_created_at_index'));
        Schema::table(config('activitylog.table_name', 'activity_log'), fn (Blueprint $t) => $t->index('created_at', 'activity_log_created_at_index'));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropIndex('users_role_deleted_at_index'));
        Schema::table('session_formations', fn (Blueprint $t) => $t->dropIndex('session_formations_code_produit_index'));
        Schema::table('seances', fn (Blueprint $t) => $t->dropIndex('seances_date_index'));
        Schema::table('ressources', fn (Blueprint $t) => $t->dropIndex('ressources_created_at_index'));
        Schema::table(config('activitylog.table_name', 'activity_log'), fn (Blueprint $t) => $t->dropIndex('activity_log_created_at_index'));
    }
};
