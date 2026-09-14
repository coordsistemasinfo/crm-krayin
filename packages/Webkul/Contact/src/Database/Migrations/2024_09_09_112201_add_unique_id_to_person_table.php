<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\Core\Database\SqlCompat;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->unique();
        });

        $tableName = DB::getTablePrefix().'persons';

        DB::statement("
            UPDATE {$tableName}
            SET unique_id = CONCAT(
                user_id, '|',
                organization_id, '|',
                COALESCE(".SqlCompat::jsonArrayFirstText($tableName.'.emails', 'value').", ''), '|',
                COALESCE(".SqlCompat::jsonArrayFirstText($tableName.'.contact_numbers', 'value').", '')
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('unique_id');
        });
    }
};
