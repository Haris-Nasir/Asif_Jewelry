<?php

use App\Services\DataCleanupService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deletes all user-entered operational data entries on deployment while preserving logins and system setup.
     *
     * @return void
     */
    public function up()
    {
        DataCleanupService::clearUserData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Deletions cannot be rolled back
    }
};
