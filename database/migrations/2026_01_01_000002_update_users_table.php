<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Inscription possible par téléphone seul → email facultatif.
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('role')->default('worker')->after('phone'); // worker | employer | admin
            $table->boolean('is_verified')->default(false)->after('role');
            $table->string('avatar')->nullable()->after('is_verified');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'is_verified', 'avatar', 'phone_verified_at']);
        });
    }
};
