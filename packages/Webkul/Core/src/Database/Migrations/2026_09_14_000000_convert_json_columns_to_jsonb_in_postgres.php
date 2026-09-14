<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\Core\Database\SqlCompat;

return new class extends Migration
{
    /**
     * Every json column shipped by the Krayin packages, grouped by table.
     */
    protected array $jsonColumns = [
        'activities' => ['additional'],
        'attribute_values' => ['json_value'],
        'workflows' => ['conditions', 'actions'],
        'webhooks' => ['query_params', 'headers', 'payload'],
        'organizations' => ['address'],
        'persons' => ['emails', 'contact_numbers'],
        'saved_filters' => ['applied'],
        'imports' => ['errors', 'summary'],
        'import_batches' => ['data', 'summary'],
        'emails' => ['folders', 'from', 'sender', 'reply_to', 'cc', 'bcc', 'reference_ids'],
        'quotes' => ['billing_address', 'shipping_address'],
        'roles' => ['permissions'],
        'warehouses' => ['contact_emails', 'contact_numbers', 'contact_address'],
    ];

    /**
     * Run the migrations.
     *
     * PostgreSQL cannot compare or deduplicate the `json` type (no equality
     * operator), so queries that use DISTINCT or GROUP BY over rows containing
     * these columns fail with "could not identify an equality operator for
     * type json". The `jsonb` type does support equality, so on PostgreSQL all
     * json columns in the active schema are converted to jsonb. MySQL/MariaDB
     * keep their `json` type.
     */
    public function up(): void
    {
        if (! SqlCompat::isPostgres()) {
            return;
        }

        $columns = DB::select(
            "SELECT table_name, column_name
            FROM information_schema.columns
            WHERE table_schema = ANY(current_schemas(false))
                AND data_type = 'json'"
        );

        foreach ($columns as $column) {
            DB::statement(
                "ALTER TABLE \"{$column->table_name}\" ALTER COLUMN \"{$column->column_name}\" TYPE jsonb USING \"{$column->column_name}\"::jsonb"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! SqlCompat::isPostgres()) {
            return;
        }

        $tablePrefix = DB::getTablePrefix();

        foreach ($this->jsonColumns as $table => $columns) {
            foreach ($columns as $column) {
                $exists = Schema::hasColumns($tablePrefix.$table, [$column]);

                if (! $exists) {
                    continue;
                }

                DB::statement(
                    "ALTER TABLE {$tablePrefix}{$table} ALTER COLUMN {$column} TYPE json USING {$column}::json"
                );
            }
        }
    }
};
