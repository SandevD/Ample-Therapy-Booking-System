<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->uuid('booking_group_id')
                ->nullable()
                ->after('notes')
                ->index()
                ->comment('Groups multi-session appointments together');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['booking_group_id']);
            $table->dropColumn('booking_group_id');
        });
    }
};
