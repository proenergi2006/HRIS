<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whistleblower_reports', function (Blueprint $table) {
            $table->string('branch_location', 100)->nullable()->after('category');
            $table->string('reporter_relation', 100)->nullable()->after('branch_location');
            $table->text('incident_location_time')->nullable()->after('description');
            $table->text('suspected_parties')->nullable()->after('incident_location_time');
            $table->text('witnesses')->nullable()->after('suspected_parties');
            $table->string('previously_reported', 20)->nullable()->after('reporter_phone');
            $table->boolean('willing_to_be_contacted')->nullable()->after('previously_reported');
        });
    }

    public function down(): void
    {
        Schema::table('whistleblower_reports', function (Blueprint $table) {
            $table->dropColumn([
                'branch_location',
                'reporter_relation',
                'incident_location_time',
                'suspected_parties',
                'witnesses',
                'previously_reported',
                'willing_to_be_contacted',
            ]);
        });
    }
};
