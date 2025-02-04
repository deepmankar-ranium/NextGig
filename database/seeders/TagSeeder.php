<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;
use Database\Factories\TagFactory;

class TagSeeder extends Seeder

{
    public function run()
    {
        // Create 10 tags (you can adjust the number as needed)
        TagFactory::new()->count(10)->create();
    }
}


