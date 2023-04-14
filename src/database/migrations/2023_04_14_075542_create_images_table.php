<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    private const TABLE_IMAGES = 'u_images';
    private const TABLE_RECIPE_IMAGES = 'u_recipe_images';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(self::TABLE_IMAGES, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('path');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create(self::TABLE_RECIPE_IMAGES, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id')->index();
            $table->unsignedBigInteger('image_id')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(self::TABLE_RECIPE_IMAGES);
        Schema::dropIfExists(self::TABLE_IMAGES);
    }
};
