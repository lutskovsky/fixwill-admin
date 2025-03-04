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
        Schema::create('sip_lines', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->integer('employee_id');
            $table->string('phone_number');
            $table->string('virtual_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sip_lines');
    }
};
