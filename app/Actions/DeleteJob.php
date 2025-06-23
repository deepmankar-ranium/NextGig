<?php

namespace App\Actions;

use App\Models\JobListing;

class DeleteJob
{
    /**
     * Delete the given job listing.
     *
     * @param JobListing $jobListing
     * @return bool
     */
    public function handle(JobListing $jobListing): bool
    {
        return (bool) $jobListing->delete();
    }
} 