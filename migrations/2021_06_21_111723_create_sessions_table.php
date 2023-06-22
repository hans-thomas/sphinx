<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {

		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up(): void {
			Schema::create( 'sessions', function( Blueprint $table ) {
				$table->id();

				$table->morphs( 'sessionable' );
				$table->unsignedInteger( 'sessionable_version' )->default( 1 );

				$table->string( 'ip', 100 );
				$table->string( 'device', 100 );
				$table->string( 'browser', 100 );
				$table->string( 'os', 100 );
				$table->string( 'secret', 512 );

				$table->timestamps();
			} );
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down(): void {
			Schema::dropIfExists( 'sessions' );
		}

	};
