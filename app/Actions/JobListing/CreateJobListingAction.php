<?php

namespace App\Actions\JobListing;

use App\Models\JobListing;

class CreateJobListingAction
{
    public function execute(array $data): JobListing
    {
        $jobListing = JobListing::create($data);

        if (isset($data['tags'])) {
            $jobListing->tags()->sync($data['tags']);
        }

        return $jobListing;
    }
}
