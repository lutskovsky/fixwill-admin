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
        Schema::table('statuses', function (Blueprint $table) {
            $table->boolean('reschedule_before')->default(false);
            $table->boolean('reschedule_after')->default(false);
            $table->boolean('refusal_before')->default(false);
            $table->boolean('refusal_after')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('reschedule_before');
            $table->dropColumn('reschedule_after');
            $table->dropColumn('refusal_before');
            $table->dropColumn('refusal_after');
        });
    }
};
