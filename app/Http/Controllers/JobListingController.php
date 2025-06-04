<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Services\JobListingService;
use App\Services\JobFilterService;
use Inertia\Response;
use App\Actions\UpdateJobListing;
use App\Http\Requests\UpdateJobListingRequest;
use App\Http\Requests\StoreJobRequest;
use App\Actions\StoreJob;


class JobListingController extends Controller
{
    public function index(JobListingService $jobListingService)
    {
        $jobListings = $jobListingService->index();
        
  
        return Inertia::render('Welcome', [
            'jobListings' => $jobListings,
        ]);
    }
    


    public function jobs(JobListingService $jobListingService){
        $user = Auth::user();
    
        $jobListings = $jobListingService->getJobListingsWithRelations();
        $tags = $jobListingService->getAllTags();
    
        $isEmployer = $user->isEmployer();
    
        return Inertia::render('Jobs', [
            'jobListings' => $jobListings,
            'isEmployer' => $isEmployer,
            'tags' => $tags,
        ]);
    }

    public function filterJobs(Request $request, JobFilterService $jobFilterService)
    {
        $tag = $request->query('tag');
    
        $jobListings = $jobFilterService->filterByTag($tag);
        dd($tag,$jobListings);
    
        return response()->json([
            'jobListings' => $jobListings
        ]);
    }


    public function show($id, JobListingService $jobListingService)
{
    $job = $jobListingService->getJobById($id);

    $user = Auth::user();

    // Check if the user is a job seeker
    $isJobSeeker = $user->isJobSeeker();

    $application = $jobListingService->appliedJobs($isJobSeeker, $user,$id);

    // Get application status safely using model constants
    $applicationStatus = $application ? $application->application_status : null;

    // Check if the user is authorized to edit the job listing
    $isOwner = Gate::allows('edit', $job);

    return Inertia::render('JobDetails', [
        'job' => $job,
        'isOwner' => $isOwner,
        'isJobSeeker' => $isJobSeeker,
        'applicationStatus' => $applicationStatus,
    ]);
}



    public function create()
    {
        $user = Auth::user();
    
        if (!$user || !$user->isEmployer()) {

            abort(403, 'Unauthorized action.');
        }
    
        $employer = $user->employer; 
        $tags = Tag::all();
       
    
        return Inertia::render('Jobs/Create', [
            'employer' => $employer,
            'tags' => $tags,
        ]);
    }
    public function store(StoreJobRequest $request, StoreJob $storeJob)
    {
        $validatedData = $request->validated();
        $jobListing = $storeJob->store($validatedData);

        // Handle tags if provided
        if (isset($validatedData['tags'])) {
            $jobListing->tags()->sync($validatedData['tags']);
        }

        return redirect('/Jobs')->with('success', 'Job listing created successfully!');
    }

    public function edit(JobListing $jobListing, JobListingService $jobListingService)  
    {
        $jobListing->load(['employer', 'tags']); // Load employer & tags
    
        $this->authorize('edit', $jobListing);

        return Inertia::render('Jobs/Edit', [
            'job' => $jobListing,
            'tags' => Tag::all(), // Fetch all tags for checkboxes
        ]);
    }
    
    
    
    public function update(UpdateJobListingRequest $request, JobListing $jobListing, UpdateJobListing $updateJobListing)
    {
        $this->authorize('edit', $jobListing);
    
        $updateJobListing->handle($jobListing, $request->validated());
    
        return redirect("/Jobs/job/{$jobListing->id}")
            ->with('success', 'Job listing updated successfully!');
    }
    
    public function destroy(JobListing $jobListing)  
    {
        $this->authorize('edit', $jobListing); 
        
        $jobListing->delete();
        
        return redirect("/Jobs")->with('success', 'Job deleted successfully!');
    }
    
   public function search(Request $request, JobListingService $jobListingService)
   {
    $searchResults = $jobListingService->search($request);

    return response()->json(['searchResults' => $searchResults]);
   }


    }
    
 

