<?php

namespace App\Actions\JobListing;

use App\Models\JobListing;
use Illuminate\Support\Facades\Gate;

class DeleteJobListingAction
{
    public function execute(JobListing $jobListing): void
    {
        Gate::authorize('delete', $jobListing);
        $jobListing->delete();
    }
}
