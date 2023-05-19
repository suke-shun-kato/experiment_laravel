<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    private const TABLE_NAME = 'u_recipe_images';

    public function up(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->renameColumn('recipe_id', 'u_recipe_id');
            $table->renameColumn('image_id', 'u_image_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->renameColumn('u_recipe_id', 'recipe_id');
            $table->renameColumn('u_image_id', 'image_id');
        });
    }
};
