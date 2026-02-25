<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hardening migration.
 *
 * Some environments (especially PostgreSQL) can contain NULL states if inserts skipped the field.
 * Ensure existing rows are backfilled and future inserts have a safe default.
 */
class FixCalendarItemsStateDefault extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		// Backfill any NULL states.
		DB::table( 'calendar_items' )->whereNull( 'state' )->update( [ 'state' => 'active' ] );

		$driver = DB::getDriverName();

		if ( $driver === 'pgsql' ) {
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN state SET DEFAULT 'active'" );
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN state SET NOT NULL" );
		} elseif ( $driver === 'mysql' ) {
			DB::statement( "ALTER TABLE calendar_items MODIFY state VARCHAR(255) NOT NULL DEFAULT 'active'" );
		}
		// SQLite: altering column defaults isn't reliable without rebuilding the table.
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		$driver = DB::getDriverName();

		if ( $driver === 'pgsql' ) {
			DB::statement( "ALTER TABLE calendar_items ALTER COLUMN state DROP DEFAULT" );
		} elseif ( $driver === 'mysql' ) {
			DB::statement( 'ALTER TABLE calendar_items MODIFY state VARCHAR(255) NOT NULL' );
		}
	}
}
