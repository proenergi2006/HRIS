<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')
                  ->constrained()->nullOnDelete();
        });

        // Tag semua karyawan yang ada sebagai Pro Energi
        $proenergiId = DB::table('companies')->where('code', 'proenergi')->value('id');
        if ($proenergiId) {
            DB::table('employees')->update(['company_id' => $proenergiId]);
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
