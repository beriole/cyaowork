<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('provider');
            $table->json('meta')->nullable()->after('status'); // ex: {plan: pro} ou {offer_id: 5}
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['phone', 'meta']);
        });
    }
};
