<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('only_for_clubs');
            $table->dateTime('registration_end_date_time');
            $table->enum('total_slots', [8, 16, 32, 64, 128, 256]);
            $table->enum('status', ['drafted', 'opened', 'closed', 'rounded', 'scheduled', 'started', 'completed'])->default('drafted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournaments');
    }
}
