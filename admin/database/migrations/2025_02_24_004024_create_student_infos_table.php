<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentInfosTable extends Migration
{
    public function up()
    {
        Schema::create('student_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('studentId')->unique();
            $table->string('fName');
            $table->string('lName');
            $table->string('program');
            $table->string('department');
            $table->string('yearLevel');
            $table->string('semester');  // semester info (e.g., "2nd Semester 2024-2025")
            $table->longText('image');   // image column as long text
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_infos');
    }
}
