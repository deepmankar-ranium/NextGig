<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            // Original values, pinned so this historical migration doesn't change when the
            // model's stages do (see 2026_11_17_000007_use_pipeline_stages_for_applications).
            $table->enum('application_status', ['pending', 'approved', 'rejected'])
                  ->default('pending');
      
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('application_status');
        });
    }
};
