<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Delete conversation" hides messages only for the user who deleted them.
     */
    public function up(): void
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->timestamp('sender_hidden_at')->nullable()->after('read_at');
            $table->timestamp('receiver_hidden_at')->nullable()->after('sender_hidden_at');
        });
    }

    public function down(): void
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->dropColumn(['sender_hidden_at', 'receiver_hidden_at']);
        });
    }
};
