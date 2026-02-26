<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixCalendarItemsStateDefault extends Migration {
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up() {
				$driver = DB::getDriverName();

				if ( $driver === 'pgsql' ) {
						// Backfill existing NULLs.
						DB::statement( "UPDATE calendar_items SET state = 'active' WHERE state IS NULL" );

						// Ensure DEFAULT for inserts.
						DB::statement( "ALTER TABLE calendar_items ALTER COLUMN state SET DEFAULT 'active'" );
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
						DB::statement( "ALTER TABLE calendar_items ALTER COLUMN state DROP DEFAULT" );
				}
		}
}
