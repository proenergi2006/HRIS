<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursement_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('payment_month')->nullable()->after('approved_at');
            $table->unsignedSmallInteger('payment_year')->nullable()->after('payment_month');
        });
    }

    public function down(): void
    {
        Schema::table('reimbursement_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_month', 'payment_year']);
        });
    }
};
