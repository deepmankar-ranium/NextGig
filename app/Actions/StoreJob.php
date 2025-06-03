<?php
namespace App\Actions;
use App\Models\JobListing;
use Illuminate\Support\Arr;

class StoreJob{
    public function store($validatedData){
        // Create the job listing
        $jobListing = JobListing::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'salary' => $validatedData['salary'],
            'employer_id' => $validatedData['employer_id'],
            'tags' => $validatedData['tags'],
        ]);
        return $jobListing;
    }
}

