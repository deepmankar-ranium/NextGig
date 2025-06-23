<?php 
namespace App\Services;
use App\Models\JobListing;
use Illuminate\Support\Facades\Auth;

class PostedJobService {
    public function getPostedJob($employerId) {
        $JobListing = JobListing::with(['employer'])
            ->where('employer_id', $employerId)  
            ->latest()
            ->paginate(6);


        return $JobListing;

}
}
