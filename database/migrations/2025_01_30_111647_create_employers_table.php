<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\grayprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employers', function (grayprint $table) {
            $table->id()->comment('Unique Employer ID');
            $table->string('name')->comment('Employer Name');
            $table->string('email')->unique()->comment('Employer Email');
            $table->string('address')->comment('Employer Address');
            $table->string('phone')->nullable()->comment('Employer Phone Number'); // New field
            $table->text('description')->nullable()->comment('Employer Description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
}

