<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('headline')->nullable();          // métier affiché
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->string('availability')->default('immediate'); // immediate | week | flexible
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->string('salary_period')->default('day');  // hour | day | month
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('verification_status')->default('pending'); // pending | verified | rejected
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamps();
        });

        Schema::create('employer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('type')->default('individual'); // individual | company
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('verification_status')->default('pending');
            $table->timestamps();
        });

        Schema::create('profile_skill', function (Blueprint $table) {
            $table->foreignId('worker_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['worker_profile_id', 'skill_id']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // cni | cv | diplome | passeport
            $table->string('file_path');
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('profile_skill');
        Schema::dropIfExists('employer_profiles');
        Schema::dropIfExists('worker_profiles');
    }
};
