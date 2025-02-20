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
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');  // Reference to the admin
            $table->string('action');  // The action (create, update, delete, login, logout)
            $table->string('model');   // The model being affected (e.g., User, Post)
            $table->string('model_id'); // The ID of the model instance affected
            $table->text('details')->nullable(); // Any extra details (like old values or changes made)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
