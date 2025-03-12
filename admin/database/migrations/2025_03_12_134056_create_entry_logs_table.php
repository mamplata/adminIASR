<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entry_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('uid');
            $table->string('student_id');
            $table->enum('time_type', ['IN', 'OUT']);
            $table->timestamp('timestamp');
            $table->enum('status', ['Success', 'Failure']);
            $table->string('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_logs');
    }
};
