<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idea_applications', function (Blueprint $table) {
            $table->index(['idea_id', 'user_id', 'status'], 'idea_applications_idea_user_status_index');
        });
    }
};
