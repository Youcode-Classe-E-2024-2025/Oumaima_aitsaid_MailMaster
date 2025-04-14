<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaign_stats', function (Blueprint $table) {
            $table->string('tracking_token')->nullable()->after('subscriber_id');
            $table->index('tracking_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_stats', function (Blueprint $table) {
            $table->dropColumn('tracking_token');
        });
    }
};
