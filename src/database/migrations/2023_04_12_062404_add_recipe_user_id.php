<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE_NAME_OLD = 'recipes';
    private const TABLE_NAME_NEW = 'u_recipes';
    private const COLUMN_NAME_USER_ID = 'user_id';

    private const COLUMN_NAME_ID = 'id';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(self::TABLE_NAME_OLD, function (Blueprint $table) {
            $table->unsignedBigInteger(self::COLUMN_NAME_USER_ID)
                ->after(self::COLUMN_NAME_ID)
                ->index();
        });

        Schema::rename(self::TABLE_NAME_OLD, self::TABLE_NAME_NEW);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename(self::TABLE_NAME_NEW, self::TABLE_NAME_OLD);

        Schema::table(self::TABLE_NAME_OLD, function (Blueprint $table) {
            $table->dropColumn(self::COLUMN_NAME_USER_ID);
        });
    }
};
