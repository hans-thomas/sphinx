<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateSessionsTable extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() {
            Schema::create( 'sessions', function( Blueprint $table ) {
                $table->id();
                $table->foreignId( 'user_id' )->constrained();
                $table->string( 'ip', 100 );
                $table->string( 'device', 100 );
                $table->string( 'platform', 100 );
                $table->string( 'secret', 512 );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() {
            Schema::dropIfExists( 'sessions' );
        }
    }
