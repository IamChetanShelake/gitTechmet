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
        Schema::table('hallenquirys', function (Blueprint $table) {
            $table->string('rules_print_file')->nullable()->after('quotation_file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hallenquirys', function (Blueprint $table) {
            $table->dropColumn('rules_print_file');
        });
    }
};
