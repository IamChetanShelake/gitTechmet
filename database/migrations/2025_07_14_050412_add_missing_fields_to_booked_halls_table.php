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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('booked_halls', 'booking_code')) {
                $table->string('booking_code')->unique()->after('event_flag');
            }
            if (!Schema::hasColumn('booked_halls', 'total_deposit')) {
                $table->decimal('total_deposit', 10, 2)->nullable()->after('total_rent');
            }
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
            if (Schema::hasColumn('booked_halls', 'booking_code')) {
                $table->dropColumn('booking_code');
            }
            if (Schema::hasColumn('booked_halls', 'total_deposit')) {
                $table->dropColumn('total_deposit');
            }
        });
    }
};