<?php

namespace App\Actions\JobListing;

use App\Models\JobListing;

class UpdateJobListingAction
{
    public function execute(JobListing $jobListing, array $data): JobListing
    {
        $jobListing->update($data);

        if (isset($data['tags'])) {
            $jobListing->tags()->sync($data['tags']);
        }

        return $jobListing;
    }
}
