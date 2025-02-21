<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('department')->nullable(); // e.g., 'ccs', 'coe', 'cbaa', or null for general
            $table->string('publisher');              // who is publishing
            $table->string('type')->default('text');  // 'text' or 'image'
            $table->json('content');                  // stored as JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
