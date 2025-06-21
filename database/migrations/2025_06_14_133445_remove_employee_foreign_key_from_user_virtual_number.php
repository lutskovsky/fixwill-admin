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
        Schema::table('user_virtual_number', function (Blueprint $table) {
            // Raw DB because some problem on the server
            DB::statement('ALTER TABLE user_virtual_number DROP FOREIGN KEY IF EXISTS employee_virtual_number_employee_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_virtual_number', function (Blueprint $table) {
            $table->foreign('user_id', 'employee_virtual_number_employee_id_foreign')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }
};
