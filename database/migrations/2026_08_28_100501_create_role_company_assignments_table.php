<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sumber kebenaran untuk "role X berlaku di company mana untuk user ini".
     * company_id NULL = role berlaku di semua company (dipakai role lintas-company
     * seperti Super Admin / HR Group Admin). model_has_roles (spatie) tetap disinkron
     * terpisah (union nama role di company manapun) supaya hasRole()/hasAnyRole() yang
     * belum sempat dikonversi ke permission tidak diam-diam rusak.
     */
    public function up(): void
    {
        Schema::create('role_company_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_company_assignments');
    }
};
