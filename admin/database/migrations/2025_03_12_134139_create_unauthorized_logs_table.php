<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unauthorized_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('uid'); // RFID card identifier
            $table->enum('time_type', ['IN', 'OUT']);
            $table->timestamp('timestamp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unauthorized_logs');
    }
};
