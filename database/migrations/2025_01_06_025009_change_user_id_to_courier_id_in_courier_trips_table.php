<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserIdToCourierIdInCourierTripsTable extends Migration
{
    public function up()
    {
        Schema::table('courier_trips', function (Blueprint $table) {
            // 1. Drop the old foreign key to users (if you have one).
            //    Make sure to drop the foreign key by its correct name if you used a custom name.
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // 2. Add the new courier_id column
            $table->unsignedBigInteger('courier_id')->after('id')->nullable();

            // 3. Create a foreign key reference to couriers(id)
            $table->foreign('courier_id')
                ->references('id')
                ->on('couriers')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('courier_trips', function (Blueprint $table) {
            // Reverse the above operations

            // Drop the new foreign key
            $table->dropForeign(['courier_id']);
            $table->dropColumn('courier_id');

            // Recreate the old user_id column
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
}
