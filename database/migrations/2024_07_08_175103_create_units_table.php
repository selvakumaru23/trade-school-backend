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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->boolean('exists_in_airtable')->default(true);
            // relationships
            $table->string('airtable_id')->unique();
            $table->integer('campaign_id'); // relationship
            $table->integer('placement_id'); // relationship
            // fields
            $table->string('name')->nullable();
            $table->string('uniqueid')->nullable();
            $table->string('filename')->nullable();
            $table->string('cta')->nullable();
            $table->string('linkingdestination')->nullable();
            $table->text('copydirection')->nullable();
            $table->text('visualdirection')->nullable();
            $table->text('tradeschoolstrategy')->nullable();
            // Generation fields
            $table->boolean('generation_copy_complete')->default(false);
            $table->boolean('generation_images_complete')->default(false);
            $table->json('generated_copy');  // JSON type cannot have a default value

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
