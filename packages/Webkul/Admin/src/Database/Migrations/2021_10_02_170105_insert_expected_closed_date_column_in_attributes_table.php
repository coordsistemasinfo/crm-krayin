<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Webkul\Core\Database\SqlCompat;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('attributes')->insert([
            [
                'id' => '7',
                'code' => 'expected_close_date',
                'name' => 'Expected Close Date',
                'type' => 'date',
                'entity_type' => 'leads',
                'lookup_type' => null,
                'validation' => null,
                'sort_order' => '8',
                'is_required' => '0',
                'is_unique' => '0',
                'quick_add' => '1',
                'is_user_defined' => '0',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        if (SqlCompat::isPostgres()) {
            DB::statement("SELECT setval(pg_get_serial_sequence('attributes', 'id'), COALESCE((SELECT MAX(id) FROM attributes), 0) + 1, false)");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
