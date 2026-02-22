<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class FixCalendarItemsStateDefault extends Migration
{
    public function up()
    {
        // Backfill any NULL states (PostgreSQL can contain NULLs if inserts skipped the field)
        DB::table('calendar_items')->whereNull('state')->update(['state' => 'active']);

        // Set a default at the DB level so future inserts are safe across DB engines
        Schema::table('calendar_items', function (Blueprint $table) {
            // Laravel doesn't support altering existing columns without doctrine/dbal.
            // Use raw SQL below when possible.
        });

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE calendar_items ALTER COLUMN state SET DEFAULT 'active'");
            // Keep NOT NULL as-is; if it was relaxed, enforce it.
            DB::statement("ALTER TABLE calendar_items ALTER COLUMN state SET NOT NULL");
        } elseif ($driver === 'mysql') {
            // MySQL: make sure default exists; keep column NOT NULL
            DB::statement("ALTER TABLE calendar_items MODIFY state VARCHAR(255) NOT NULL DEFAULT 'active'");
        } elseif ($driver === 'sqlite') {
            // SQLite can't reliably alter column defaults without table rebuild; backfill is still valuable.
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE calendar_items ALTER COLUMN state DROP DEFAULT");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE calendar_items MODIFY state VARCHAR(255) NOT NULL");
        }
    }
}
