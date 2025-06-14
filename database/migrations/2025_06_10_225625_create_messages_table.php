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
        Schema::create('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('chat_id');
            $table->text('text');
            $table->enum('source', ['operator', 'visitor']);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->index('chat_id');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
