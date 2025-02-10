<?php
// database/migrations/[...]_create_status_changes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('status_changes', function (Blueprint $table) {
            $table->id();
            $table->integer('new_status_id');
            $table->integer('old_status_id');
            $table->integer('order_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('status_changes');
    }
};
