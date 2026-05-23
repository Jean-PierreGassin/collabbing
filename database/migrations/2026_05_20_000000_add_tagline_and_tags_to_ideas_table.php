<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->string('tagline', 140)->nullable()->after('title');
            $table->json('tags')->nullable()->after('summary');
        });
    }
};
