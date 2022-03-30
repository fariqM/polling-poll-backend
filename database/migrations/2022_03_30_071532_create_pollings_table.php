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
            $table->text('question');
            $table->text('description');
            $table->date('deadline')->nullable();
            $table->boolean('with_password');
            $table->string('password')->nullable();
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
