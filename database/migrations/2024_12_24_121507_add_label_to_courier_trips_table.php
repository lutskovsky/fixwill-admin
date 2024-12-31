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
        Schema::table('courier_trips', function (Blueprint $table) {
            $table->string("order_label")->default('1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courier_trips', function (Blueprint $table) {
            $table->dropColumn("order_label");
        });
    }
};
