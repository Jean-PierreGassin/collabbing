<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ideas')
            ->select(['id', 'tagline'])
            ->whereNotNull('tagline')
            ->orderBy('id')
            ->chunkById(100, function ($ideas): void {
                foreach ($ideas as $idea) {
                    if (mb_strlen($idea->tagline) <= 60) {
                        continue;
                    }

                    DB::table('ideas')
                        ->where('id', $idea->id)
                        ->update([
                            'tagline' => mb_substr($idea->tagline, 0, 60),
                        ]);
                }
            });

        Schema::table('ideas', function (Blueprint $table) {
            $table->string('tagline', 60)->nullable()->change();
        });
    }
};
