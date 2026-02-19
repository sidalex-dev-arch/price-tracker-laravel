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
        Schema::create(
            "track_links", function (Blueprint $table) {
                $table->id();
                $table->foreignId("track_id")->constrained()->cascadeOnDelete();
                $table->string("store_name");
                $table->string("url")->unique();
                $table->decimal("last_price", 10, 2)->nullable();
                $table->timestamp("last_checked_at")->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("track_links");
    }
};