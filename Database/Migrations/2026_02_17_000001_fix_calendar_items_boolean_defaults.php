<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hardening migration.
 *
 * Some environments (especially PostgreSQL) can throw a NOT NULL violation if an INSERT into
 * calendar_items omits boolean columns that are NOT NULL but have no DEFAULT.
 *
 * This migration:
 *  - Backfills any existing NULLs to false
 *  - Ensures sensible DEFAULTs for booleans (PostgreSQL)
 *  - Re-applies NOT NULL for safety (PostgreSQL)
 */
class FixCalendarItemsBooleanDefaults extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		$driver = DB::getDriverName();

		if ( $driver === 'pgsql' ) {
			DB::statement( "UPDATE calendar_items SET is_private = false WHERE is_private IS NULL" );
			DB::statement( "UPDATE calendar_items SET is_all_day = false WHERE is_all_day IS NULL" );
			DB::statement( "UPDATE calendar_items SET is_read_only = false WHERE is_read_only IS NULL" );

			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_private SET DEFAULT false" );
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_all_day SET DEFAULT false" );
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_read_only SET DEFAULT false" );

			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_private SET NOT NULL" );
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_all_day SET NOT NULL" );
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_read_only SET NOT NULL" );
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		$driver = DB::getDriverName();

		if ( $driver === 'pgsql' ) {
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_private DROP DEFAULT" );
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_all_day DROP DEFAULT" );
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN is_read_only DROP DEFAULT" );
		}
	}
}
