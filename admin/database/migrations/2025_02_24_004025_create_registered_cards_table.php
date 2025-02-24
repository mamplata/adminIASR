<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegisteredCardsTable extends Migration
{
    public function up()
    {
        Schema::create('registered_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('studentId');
            $table->foreign('studentId')->references('studentId')->on('student_infos');
            $table->string('uid')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('registered_cards');
    }
}
