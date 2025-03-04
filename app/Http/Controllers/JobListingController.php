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


use Inertia\Response;

class JobListingController extends Controller
{
    public function index()
    {
        $jobListings = JobListing::with(['employer', 'tags'])->paginate(6);
  
        return Inertia::render('Welcome', [
            'jobListings' => $jobListings,
        ]);
    }
    

public function jobs()
{
    $user = Auth::user();
    $jobListings = JobListing::with(['employer', 'tags'])->latest()->paginate(6);
    $isEmployer = $user && $user->role && $user->role->name === "Employer";
    $tags = Tag::all();

    return Inertia::render('Jobs', [
        'jobListings' => $jobListings,
        'isEmployer' => $isEmployer,
        'tags' => $tags,
    ]);
}

public function filterJobs(Request $request)
{
    $tag = $request->query('tag');



    $jobListings = JobListing::with(['employer', 'tags'])
        ->when($tag, function ($query) use ($tag) {
            return $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', $tag);
            });
        })
        ->latest()
        ->paginate(6);

    return response()->json([
        'jobListings' => $jobListings
    ]);
}


public function show($id)
{
    $job = JobListing::with('employer.user')->findOrFail($id);
    $user = Auth::user();

    // Check if the user is a job seeker
    $isJobSeeker = optional($user->role)->name === "Job Seeker";

    // Fetch the application where the authenticated job seeker has applied
    $application = $isJobSeeker && $user->jobseeker 
        ? Application::where('jobseeker_id', $user->jobseeker->id)
            ->where('joblisting_id', $id)
            ->first()
        : null;

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
    
        if ($user->role->name !== 'Employer') {
            abort(403, 'Unauthorized action.');
        }
    
        $employer = $user->employer; 
        $tags = Tag::all();
      
    
        return Inertia::render('Jobs/Create', [
            'employer' => $employer,
            'tags' => $tags,
        ]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'employer_id' => 'required|exists:employers,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id', // Ensures each tag exists in the `tags` table
        ]);
    
        // Create the job listing
        $jobListing = JobListing::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'salary' => $validatedData['salary'],
            'employer_id' => $validatedData['employer_id'],
        ]);
    
        // Handle tags if provided
        if (isset($validatedData['tags'])) {
            $jobListing->tags()->sync($validatedData['tags']);
        }
    
        return redirect('/Jobs')->with('success', 'Job listing created successfully!');
    }

    public function edit(JobListing $jobListing)  
    {
        $jobListing->load(['employer', 'tags']); // Load employer & tags
    
        $this->authorize('edit', $jobListing);

        return Inertia::render('Jobs/Edit', [
            'job' => $jobListing,
            'tags' => Tag::all(), // Fetch all tags for checkboxes
        ]);
    }
    
    
    
    public function update(Request $request, JobListing $jobListing)  
    {
        $this->authorize('edit', $jobListing);
        
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'employer_id' => 'required|exists:employers,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);
        
        // Update the job listing basic info
        $jobListing->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'salary' => $validatedData['salary'],
            'employer_id' => $validatedData['employer_id'],
        ]);
        
        // Handle tags if provided
        if (isset($validatedData['tags'])) {
            $jobListing->tags()->sync($validatedData['tags']);
        }
        
        return redirect("/Jobs/job/{$jobListing->id}")->with('success', 'Job listing updated successfully!');
    }
    
    public function destroy(JobListing $jobListing)  
    {
        $this->authorize('edit', $jobListing); 
        
        $jobListing->delete();
        
        return redirect("/Jobs")->with('success', 'Job deleted successfully!');
    }
    
   public function search(Request $request)
{
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
    return response()->json(['searchResults' => $searchResults]);
}
    }
    
 

