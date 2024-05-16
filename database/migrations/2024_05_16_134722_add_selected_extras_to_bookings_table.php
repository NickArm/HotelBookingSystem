<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Ensure to specify after which column you want to add the new column
            $table->json('pricing_data')->nullable()->after('total_price');
            $table->json('selected_extras')->nullable()->after('pricing_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('selected_extras');
            $table->dropColumn('pricing_data');
        });
    }
};
