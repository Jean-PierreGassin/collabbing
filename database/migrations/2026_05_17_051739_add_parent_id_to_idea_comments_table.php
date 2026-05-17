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
        Schema::table('idea_comments', function (Blueprint $table) {
            $table->unsignedInteger('parent_id')->nullable()->after('idea_id');

            $table->foreign('parent_id')
                ->references('id')
                ->on('idea_comments')
                ->nullOnDelete();

            $table->index(['idea_id', 'parent_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idea_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['idea_id', 'parent_id', 'created_at']);
            $table->dropColumn('parent_id');
        });
    }
};
