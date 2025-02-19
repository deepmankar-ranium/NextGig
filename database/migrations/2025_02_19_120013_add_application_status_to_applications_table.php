<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Application; // Import the Application model

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->enum('application_status', Application::getApplicationStatuses()) // Fetch from model
                  ->default(Application::STATUS_PENDING); // Use model constant
      
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
