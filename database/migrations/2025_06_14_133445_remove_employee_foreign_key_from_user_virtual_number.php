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
            try {
                DB::statement('ALTER TABLE user_virtual_number DROP FOREIGN KEY employee_virtual_number_employee_id_foreign');
            } catch (\Illuminate\Database\QueryException $e) {
                // Check if it's specifically the "Can't DROP" error
                if (strpos($e->getMessage(), "Can't DROP") !== false) {
                    // Foreign key doesn't exist, that's fine
                    return;
                }
                // If it's a different error, re-throw it
                throw $e;
            }
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
