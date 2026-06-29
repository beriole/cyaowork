<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('salary_amount', 12, 2)->nullable();
            $table->string('salary_period')->default('month'); // hour | day | month
            $table->string('schedule')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('contract_type')->default('permanent'); // ponctuel | journalier | permanent
            $table->string('status')->default('published'); // draft | published | filled | expired | archived
            $table->boolean('is_boosted')->default(false);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('sent'); // sent | seen | interview | accepted | rejected
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['job_offer_id', 'worker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('job_offers');
    }
};
