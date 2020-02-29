<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdeaCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'idea_comments',
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

                $table->mediumText('content');
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('idea_comments');
    }
}
