<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * pending/approved/rejected → applied → viewed → shortlisted → interview → offer / rejected.
     * Existing rows: pending → applied, approved → offer.
     */
    private const MAP = ['pending' => 'applied', 'approved' => 'offer'];

    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('application_status', 32)->default('applied')->change();
        });

        foreach (self::MAP as $old => $new) {
            DB::table('applications')->where('application_status', $old)->update(['application_status' => $new]);
            DB::table('application_status_history')->where('to_status', $old)->update(['to_status' => $new]);
            DB::table('application_status_history')->where('from_status', $old)->update(['from_status' => $new]);
        }
    }

    public function down(): void
    {
        DB::table('applications')->whereIn('application_status', ['applied', 'viewed', 'shortlisted', 'interview'])
            ->update(['application_status' => 'pending']);
        DB::table('applications')->where('application_status', 'offer')->update(['application_status' => 'approved']);

        Schema::table('applications', function (Blueprint $table) {
            $table->enum('application_status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
