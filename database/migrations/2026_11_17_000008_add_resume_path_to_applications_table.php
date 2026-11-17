<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot of the resume PDF sent with each application, so the employer sees exactly
     * what was submitted even if the seeker later replaces their resume.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('resume_path')->nullable()->after('resume_text');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('resume_path');
        });
    }
};
