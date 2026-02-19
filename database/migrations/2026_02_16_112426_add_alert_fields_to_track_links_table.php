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
        Schema::table(
            'track_links', function (Blueprint $table) {
                $table->timestamp('alert_triggered_at')->nullable()->after('last_checked_at');
                $table->decimal('alert_percentage_detected', 5, 1)->nullable()->after('alert_triggered_at');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'track_links', function (Blueprint $table) {
                //
            }
        );
    }
};