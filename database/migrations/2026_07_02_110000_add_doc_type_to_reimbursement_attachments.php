<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursement_attachments', function (Blueprint $table) {
            $table->string('doc_type', 60)->nullable()->after('file_name');
        });
    }

    public function down(): void
    {
        Schema::table('reimbursement_attachments', function (Blueprint $table) {
            $table->dropColumn('doc_type');
        });
    }
};
