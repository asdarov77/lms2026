<?php

use Illuminate\Support\Facades\Schema;

if (!function_exists('schema_has_column')) {
    /**
     * Check if a column exists in a database table
     *
     * @param string $table Table name
     * @param string $column Column name
     * @return bool
     */
    function schema_has_column($table, $column)
    {
        return Schema::hasColumn($table, $column);
    }
} 