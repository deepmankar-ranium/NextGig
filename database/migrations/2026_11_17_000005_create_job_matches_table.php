<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cached seeker x job match. `score` is computed by code (MatchScorer); `explanation` is the
     * optional AI-written text, valid only while `explanation_hash` equals `inputs_hash`.
     */
    public function up(): void
    {
        Schema::create('job_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jobseeker_id')->constrained('jobseekers')->cascadeOnDelete();
            $table->foreignId('job_listing_id')->constrained('job_listings')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->nullable();
            $table->json('matched');
            $table->json('missing');
            $table->string('inputs_hash', 64);
            $table->json('explanation')->nullable();
            $table->string('explanation_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['jobseeker_id', 'job_listing_id']);
            $table->index(['job_listing_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_matches');
    }
};
