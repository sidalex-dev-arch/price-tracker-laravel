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
    Schema::create("price_histories", function (Blueprint $table) {
        $table->id();
        $table->foreignId("track_link_id")->constrained()->cascadeOnDelete();
        $table->decimal("price", 12, 2);
        $table->string("raw_price")->nullable();
        $table->timestamp("checked_at");
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("price_histories");
    }
};