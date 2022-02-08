<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('player_id');
            $table->enum('name', [
                'first_nick', 'last_nick', 'first_last_nick'
            ])->default('first_nick');
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
        Schema::dropIfExists('player_settings');
    }
}
