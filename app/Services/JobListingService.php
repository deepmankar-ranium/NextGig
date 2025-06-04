<?php
namespace App\Services;

use App\Models\JobListing;
use App\Models\Tag;
use App\Models\Employer;
use App\Models\Application;

class JobListingService
{
    public function index(){
        $jobListings = JobListing::with(['employer', 'tags'])->paginate(6);
        return $jobListings;

    }



    public function getJobListingsWithRelations(int $perPage = 6)
    {
        return JobListing::with(['employer', 'tags'])->latest()->paginate($perPage);
    }

    public function getAllTags()
    {
        return Tag::all();
    }

    public function getJobById(int $id){
        
        return   JobListing::with(['employer.user', 'tags'])->findOrFail($id);

    }

    public function search($request){
        $query = $request->validate([
            'q' => 'nullable|string|max:255'
        ])['q'] ?? '';
    
        if (empty($query)) {
            return response()->json(['searchResults' => []]);
        }
    
        $searchResults = JobListing::with('employer.user')
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->paginate(10);
        return $searchResults;
    

    }

    public function appliedJobs($isJobSeeker,$user,$id){
            // Fetch the application where the authenticated job seeker has applied
    return $application = $isJobSeeker && $user->jobseeker 
    ? Application::where('jobseeker_id', $user->jobseeker->id)
        ->where('joblisting_id', $id)
        ->first()
    : null;

    }

    
}
