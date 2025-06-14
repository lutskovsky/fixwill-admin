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
            // Just drop the old foreign key constraint
            $table->dropForeign('employee_virtual_number_employee_id_foreign');
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
