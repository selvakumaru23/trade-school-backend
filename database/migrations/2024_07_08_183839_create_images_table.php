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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->boolean('exists_in_airtable')->default(true);
            // relationships
            $table->string('airtable_id');
            $table->integer('unit_id'); // relationship
            // fields
            $table->string('url_original', 1000)->nullable();
            $table->string('url_thumbnail_small', 1000)->nullable();
            $table->string('url_thumbnail_large', 1000)->nullable();
            $table->string('url_thumbnail_full', 1000)->nullable();
            $table->timestamps();
            // indexes
            $table->index(['airtable_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
