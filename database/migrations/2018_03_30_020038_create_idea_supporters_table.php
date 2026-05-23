<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdeaSupportersTable extends Migration
{
    public function up(): void
    {
        Schema::create(
            'idea_supporters',
            function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('user_id');
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->unsignedInteger('idea_id');
                $table->foreign('idea_id')
                    ->references('id')
                    ->on('ideas')
                    ->onDelete('cascade');

                $table->timestamps();
            }
        );
    }
}
