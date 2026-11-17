<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `resume` (existing) holds the private storage path of the uploaded PDF.
     * `profile` holds the parsed, seeker-editable profile: skills, years_experience, roles, summary.
     */
    public function up(): void
    {
        Schema::table('jobseekers', function (Blueprint $table) {
            $table->longText('resume_text')->nullable()->after('resume');
            $table->json('profile')->nullable()->after('skills');
            $table->timestamp('profile_updated_at')->nullable()->after('profile');
        });
    }

    public function down(): void
    {
        Schema::table('jobseekers', function (Blueprint $table) {
            $table->dropColumn(['resume_text', 'profile', 'profile_updated_at']);
        });
    }
};
