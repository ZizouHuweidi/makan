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
        Schema::create('amenity_listing', function (Blueprint $table) {
            $table->uuid('amenity_id');
            $table->foreign('amenity_id')->references('id')->on('amenities')->onDelete('cascade');
            $table->uuid('listing_id');
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            $table->timestamps();

            $table->primary(['amenity_id', 'listing_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenity_listing');
    }
};
