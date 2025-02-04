<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\grayprint;
use Illuminate\Support\Facades\Schema;

class AddEmployerIdToJobListingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('job_listings', function (grayprint $table) {
            $table->foreignId('employer_id')->constrained()->onDelete('cascade')->after('id'); // Add foreign key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('job_listings', function (grayprint $table) {
            $table->dropColumn('employer_id'); // Drop the employer_id column if rolling back
        });
    }
}
