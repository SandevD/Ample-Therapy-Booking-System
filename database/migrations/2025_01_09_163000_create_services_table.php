<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration')->default(60)->comment('Duration in minutes');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('buffer_time')->default(15)->comment('Buffer time in minutes between appointments');
            $table->boolean('is_active')->default(true);
            $table->string('color', 7)->default('#3B82F6')->comment('Hex color for UI');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
