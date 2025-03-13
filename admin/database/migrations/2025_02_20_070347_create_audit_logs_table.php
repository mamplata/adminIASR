<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('action');  // The action (create, update, delete, login, logout)
            $table->string('type');   // The type being affected (e.g., User, Post)
            $table->string('type_id'); // The ID of the type instance affected
            $table->text('details')->nullable(); // Any extra details (like old values or changes made)
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
