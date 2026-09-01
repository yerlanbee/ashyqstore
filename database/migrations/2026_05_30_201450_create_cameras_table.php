<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fridge_id')->nullable()->constrained('fridges')->nullOnDelete();
            $table->string('name');
            $table->string('serial')->unique();
            $table->string('verification_code')->nullable();
            $table->unsignedTinyInteger('channel_no')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cameras');
    }
};
