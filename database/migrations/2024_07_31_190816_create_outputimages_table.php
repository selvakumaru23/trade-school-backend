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
        Schema::create('outputimages', function (Blueprint $table) {
            $table->id();
            $table->integer('unit_id')->nullable();
            $table->integer('image_id')->nullable();
            $table->string('url_generated', 1000)->nullable();
            $table->string('url_final', 1000)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outputimages');
    }
};
