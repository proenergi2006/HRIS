<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pakai raw SQL untuk hindari masalah nama FK/index yang berubah antar run
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop unique lama jika masih ada
        $oldIdx = DB::select("SHOW INDEX FROM appraisal_items WHERE Key_name = 'appraisal_items_appraisal_id_appraisal_aspect_id_unique'");
        if ($oldIdx) {
            DB::statement('ALTER TABLE appraisal_items DROP INDEX appraisal_items_appraisal_id_appraisal_aspect_id_unique');
        }

        // Drop unique baru jika sudah ada (idempotent)
        $newIdx = DB::select("SHOW INDEX FROM appraisal_items WHERE Key_name = 'appraisal_items_unique'");
        if (! $newIdx) {
            DB::statement('ALTER TABLE appraisal_items ADD UNIQUE appraisal_items_unique (appraisal_id, appraisal_aspect_id, evaluator_type)');
        }

        // Re-add FK jika belum ada
        $fkAppraisal = DB::select("SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='appraisal_items' AND CONSTRAINT_NAME='appraisal_items_appraisal_id_foreign'");
        if (! $fkAppraisal) {
            DB::statement('ALTER TABLE appraisal_items ADD CONSTRAINT appraisal_items_appraisal_id_foreign FOREIGN KEY (appraisal_id) REFERENCES appraisals(id) ON DELETE CASCADE');
        }

        $fkAspect = DB::select("SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='appraisal_items' AND CONSTRAINT_NAME='appraisal_items_appraisal_aspect_id_foreign'");
        if (! $fkAspect) {
            DB::statement('ALTER TABLE appraisal_items ADD CONSTRAINT appraisal_items_appraisal_aspect_id_foreign FOREIGN KEY (appraisal_aspect_id) REFERENCES appraisal_aspects(id) ON DELETE CASCADE');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE appraisal_items DROP INDEX appraisal_items_unique');
        DB::statement('ALTER TABLE appraisal_items ADD UNIQUE (appraisal_id, appraisal_aspect_id)');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
