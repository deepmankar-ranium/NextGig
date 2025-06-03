<?php
namespace App\Actions;

use App\Models\JobListing;
use Illuminate\Support\Arr;

class UpdateJobListing
{
    public function handle(JobListing $jobListing, array $data): JobListing
    {
        $jobListing->update(Arr::only($data, [
            'title',
            'description',
            'salary',
            'employer_id',
        ]));

        if (isset($data['tags'])) {
            $jobListing->tags()->sync($data['tags']);
        }

        return $jobListing;
    }
}
