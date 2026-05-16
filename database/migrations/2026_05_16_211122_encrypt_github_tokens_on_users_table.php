<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('github_token')->nullable()->change();
        });

        DB::table('users')
            ->whereNotNull('github_token')
            ->orderBy('id')
            ->select(['id', 'github_token'])
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    if ($this->isEncrypted($user->github_token)) {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'github_token' => Crypt::encryptString($user->github_token),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->whereNotNull('github_token')
            ->orderBy('id')
            ->select(['id', 'github_token'])
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    try {
                        $token = Crypt::decryptString($user->github_token);
                    } catch (DecryptException) {
                        $token = $user->github_token;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'github_token' => $token,
                        ]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('github_token')->nullable()->change();
        });
    }

    private function isEncrypted(string $token): bool
    {
        try {
            Crypt::decryptString($token);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
