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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->boolean('exists_in_airtable')->default(true);
            // relationships
            $table->string('airtable_id')->unique();
            $table->integer('client_id'); // relationship
            $table->integer('style_id'); // relationship
            // fields
            $table->string('name')->nullable();
            $table->text('targetaudience')->nullable();
            $table->text('goal')->nullable();
            $table->text('copydirection')->nullable();
            $table->text('visualdirection')->nullable();
            $table->text('tradeschoolstrategy')->nullable();
            $table->text('linkingdestination')->nullable();
            $table->text('funnelplacement')->nullable();
            $table->text('featuredproducts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
