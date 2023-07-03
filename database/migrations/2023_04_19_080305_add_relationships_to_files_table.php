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
        Schema::table('files', function (Blueprint $table) {
            if (!Schema::hasColumn('files', 'folder_id')) {
                $table->unsignedInteger('folder_id')->nullable();
                $table->foreign('folder_id')->references('id')->on('folders');
            }
            if (!Schema::hasColumn('files', 'created_by_id')) {
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->foreign('created_by_id')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('folder_id');
            $table->dropColumn('created_by_id');
        });
    }
};
