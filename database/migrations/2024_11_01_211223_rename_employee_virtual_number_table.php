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
        Schema::rename('employee_virtual_number', 'user_virtual_number');

        Schema::table('user_virtual_number', function (Blueprint $table) {
            $table->renameColumn('employee_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('user_virtual_number', 'employee_virtual_number');


        Schema::table('employee_virtual_number', function (Blueprint $table) {
            $table->renameColumn('user_id', 'employee_id');
        });
    }
};
