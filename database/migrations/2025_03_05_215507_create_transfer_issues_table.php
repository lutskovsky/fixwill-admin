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
        Schema::create('transfer_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('type');
            $table->json('phones')->nullable();
            $table->text('description')->nullable();

            $table->boolean('called')->default(false);
            $table->boolean('processed')->default(false);
            $table->boolean('postponed')->default(false);
            $table->text('reason')->nullable();
            $table->text('result')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_issues');
    }
};
