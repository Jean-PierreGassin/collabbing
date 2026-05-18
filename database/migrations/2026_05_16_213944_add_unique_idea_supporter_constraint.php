<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idea_supporters', function (Blueprint $table) {
            $table->unique(['idea_id', 'user_id'], 'idea_supporters_idea_id_user_id_unique');
        });
    }
};
