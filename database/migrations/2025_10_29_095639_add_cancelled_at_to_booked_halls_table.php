<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booked_halls', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('remaining_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booked_halls', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });
    }
};
