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
            "tracks", function (Blueprint $table) {
                $table->id();
                $table->string("name");
                $table->text("notes")->nullable();
                $table->decimal("target_price", 10, 2)->nullable();
                $table->integer("alert_percentage")->default(10);
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("tracks");
    }
};