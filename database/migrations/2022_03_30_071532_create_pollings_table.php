<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pollings', function (Blueprint $table) {
            $table->id();
            $table->string('owner_id');
            $table->string('dir');
            $table->text('question');
            $table->text('description');
            $table->string('q_img')->nullable();
            $table->date('deadline')->nullable();
            $table->boolean('with_password');
            $table->string('password')->nullable();
            $table->boolean('with_area_res');
            $table->integer('area')->nullable();
            $table->boolean('with_device_res');
            $table->boolean('req_email')->nullable();
            $table->boolean('req_name')->nullable();
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
        Schema::dropIfExists('pollings');
    }
};
