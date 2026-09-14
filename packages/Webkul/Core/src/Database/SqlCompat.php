<?php

namespace Webkul\Core\Database;

use Illuminate\Support\Facades\DB;

/**
 * Portable SQL expression builders.
 *
 * Krayin was built against MySQL/MariaDB. When the CRM runs on PostgreSQL the
 * raw expressions produced here switch to their Postgres counterparts, keeping
 * every call site driver-agnostic.
 */
class SqlCompat
{
    /**
     * The name of the active database driver (mysql, mariadb, pgsql, sqlite...).
     */
    public static function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    /**
     * Whether the active connection is PostgreSQL.
     */
    public static function isPostgres(): bool
    {
        return self::driver() === 'pgsql';
    }

    /**
     * Whether the active connection is MySQL or MariaDB.
     */
    public static function isMySql(): bool
    {
        return in_array(self::driver(), ['mysql', 'mariadb']);
    }

    /**
     * `DATEDIFF(left, right)` — whole days between the two dates (left - right).
     */
    public static function datediff(string $left, string $right): string
    {
        if (self::isPostgres()) {
            return "(DATE($left) - DATE($right))";
        }

        return "DATEDIFF($left, $right)";
    }

    /**
     * `DATEDIFF(NOW(), column)` — whole days from the column's date until today.
     */
    public static function daysFromNow(string $column): string
    {
        if (self::isPostgres()) {
            return "(CURRENT_DATE - DATE($column))";
        }

        return "DATEDIFF(NOW(), $column)";
    }

    /**
     * `column + INTERVAL days DAY` — days may be a column or any numeric expression.
     */
    public static function dateAddDays(string $column, string $days): string
    {
        if (self::isPostgres()) {
            return "($column::date + ($days))";
        }

        return "($column + INTERVAL $days DAY)";
    }

    /**
     * `IF(condition, then, else)` — string literals must already be single-quoted
     * (double quotes mean identifiers in PostgreSQL).
     */
    public static function ifExpr(string $condition, string $then, string $else): string
    {
        if (self::isPostgres()) {
            return "(CASE WHEN $condition THEN $then ELSE $else END)";
        }

        return "IF($condition, $then, $else)";
    }

    /**
     * `GROUP_CONCAT(DISTINCT expression ORDER BY orderBy SEPARATOR ', ')`.
     */
    public static function groupConcat(
        string $expression,
        string $separator = ', ',
        bool $distinct = true,
        ?string $orderBy = null,
    ): string {
        $distinct = $distinct ? 'DISTINCT ' : '';

        $order = $orderBy ? " ORDER BY $orderBy" : '';

        $separator = str_replace("'", "''", $separator);

        if (self::isPostgres()) {
            return "string_agg($distinct$expression, '$separator'$order)";
        }

        return "GROUP_CONCAT($distinct$expression$order SEPARATOR '$separator')";
    }

    /**
     * `MONTH(column)` / `YEAR(column)` / `WEEK(column)` / `DAYOFYEAR(column)`.
     *
     * The unit is passed in its MySQL spelling.
     */
    public static function extract(string $unit, string $column): string
    {
        if (self::isPostgres()) {
            $unit = match ($unit) {
                'DAYOFYEAR' => 'DOY',
                default => $unit,
            };

            return "EXTRACT($unit FROM $column)::int";
        }

        return "$unit($column)";
    }

    /**
     * `DATE_FORMAT(column, pattern)` — pattern uses MySQL specifiers.
     *
     * Supported: %Y, %m, %d, %u.
     */
    public static function dateFormat(string $column, string $pattern): string
    {
        if (self::isPostgres()) {
            $pattern = str_replace(
                ['%Y', '%m', '%d', '%u'],
                ['YYYY', 'MM', 'DD', 'IW'],
                $pattern,
            );

            return "to_char($column, '$pattern')";
        }

        return "DATE_FORMAT($column, '$pattern')";
    }

    /**
     * `JSON_UNQUOTE(JSON_EXTRACT(column, '$."key"'))` — unquoted text of an object key.
     */
    public static function jsonGetText(string $column, string $key): string
    {
        $key = str_replace("'", "''", $key);

        if (self::isPostgres()) {
            return "($column->>'$key')";
        }

        return "JSON_UNQUOTE(JSON_EXTRACT($column, '\$.".'"'.$key.'"'."'))";
    }

    /**
     * `JSON_UNQUOTE(JSON_EXTRACT(column, '$[0].key'))` — unquoted text of a key
     * inside the first element of a JSON array.
     */
    public static function jsonArrayFirstText(string $column, string $elementKey): string
    {
        $elementKey = str_replace("'", "''", $elementKey);

        if (self::isPostgres()) {
            return "($column::jsonb->0->>'$elementKey')";
        }

        return "JSON_UNQUOTE(JSON_EXTRACT($column, '\$[0].$elementKey'))";
    }

    /**
     * Re-sync every PostgreSQL identity sequence to `MAX(id) + 1`.
     *
     * Rows inserted with explicit ids (seeders, data migrations) leave the
     * sequences behind, so the next auto-generated value would collide.
     * MySQL/MariaDB advance their auto_increment on explicit inserts, so this
     * is a no-op there.
     */
    public static function syncSequences(): void
    {
        if (! self::isPostgres()) {
            return;
        }

        $sequences = DB::select(
            "SELECT
                s.relname AS sequence_name,
                t.relname AS table_name,
                a.attname AS column_name
            FROM pg_class s
            JOIN pg_depend d
                ON d.objid = s.oid AND d.classid = 'pg_class'::regclass
            JOIN pg_class t ON t.oid = d.refobjid
            JOIN pg_attribute a
                ON a.attrelid = t.oid AND a.attnum = d.refobjsubid
            JOIN pg_namespace n ON n.oid = s.relnamespace
            WHERE s.relkind = 'S'
                AND d.deptype = 'a'
                AND n.nspname = ANY(current_schemas(false))"
        );

        foreach ($sequences as $sequence) {
            DB::statement(
                "SELECT setval('{$sequence->sequence_name}', COALESCE((SELECT MAX({$sequence->column_name}) FROM {$sequence->table_name}), 0) + 1, false)"
            );
        }
    }
}
