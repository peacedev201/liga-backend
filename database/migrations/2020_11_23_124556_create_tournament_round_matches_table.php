<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentRoundMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_round_matches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tournament_round_id');
            $table->bigInteger('first_player_id')->nullable();
            $table->bigInteger('second_player_id')->nullable();
            $table->string('held_date')->nullable();
            $table->string('held_time')->nullable();
            $table->string('first_player_score')->nullable();
            $table->string('second_player_score')->nullable();
            $table->string('first_player_points')->nullable();
            $table->string('second_player_points')->nullable();
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
        Schema::dropIfExists('tournament_round_matches');
    }
}
