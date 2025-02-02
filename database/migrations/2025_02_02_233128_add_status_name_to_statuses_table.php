<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // In the generated migration file:
    public function up()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('status_name')->nullable()->after('status_id');
        });
    }

    public function down()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('status_name');
        });
    }
};
