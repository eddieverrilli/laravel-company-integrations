<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store each key's last four characters, so pages can show them without
     * decrypting the key, and backfill the rows that already exist.
     */
    public function up(): void
    {
        // Nullable, so the existing rows don't need a value before the backfill.
        Schema::table('integrations', function (Blueprint $table) {
            $table->string('api_key_last_four', 4)->nullable()->after('api_key');
        });

        // The query builder rather than the model, so later model changes can't
        // break this migration. chunkById pages by id: rows that leave the
        // whereNull filter as they're updated can't make it skip others.
        DB::table('integrations')
            ->whereNull('api_key_last_four')
            ->select(['id', 'api_key'])
            ->chunkById(100, function ($integrations) {
                foreach ($integrations as $integration) {
                    DB::table('integrations')
                        ->where('id', $integration->id)
                        ->update(['api_key_last_four' => substr(Crypt::decryptString($integration->api_key), -4)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn('api_key_last_four');
        });
    }
};
