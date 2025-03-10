<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class CreateEmployersTable extends Migration
{
    public function up()
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id()->comment('Unique Employer ID');
            $table->foreignIdFor(User::class)
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('User ID who owns the employer');
            $table->string('name')->comment('Employer Name');
            $table->string('email')->unique()->comment('Employer Email');
            $table->string('address')->comment('Employer Address');
            $table->string('phone')->nullable()->comment('Employer Phone Number');
            $table->text('description')->nullable()->comment('Employer Description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
}