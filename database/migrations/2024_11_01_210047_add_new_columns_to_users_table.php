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
        Schema::table('users', function (Blueprint $table) {
            $table->string('internal_phone')->nullable();
            $table->string('remonline_login')->nullable();
            $table->string('tg_login')->nullable();
            $table->string('chat_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('internal_phone');
            $table->dropColumn('remonline_login');
            $table->dropColumn('tg_login');
            $table->dropColumn('chat_id');
        });
    }
};
