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
        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->boolean('exists_in_airtable')->default(true);
            // relationships
            $table->string('airtable_id')->unique();
            $table->integer('provider_id'); // relationship
            // fields
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('mediatype')->nullable();
            $table->string('platformtype')->nullable();
            $table->string('tradeschoolstrategy')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placements');
    }
};
