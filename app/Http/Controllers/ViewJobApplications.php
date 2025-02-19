<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ViewJobApplications extends Controller
{
    public function show()
    {
        $user = Auth::user();
    
        // Ensure user is authenticated and is an Employer
        if (!$user || !$user->role || $user->role->name !== 'Employer') {
            abort(403, 'Unauthorized action.');
        }
    
        // Retrieve only applications for jobs posted by this employer
        $applications = Application::whereHas('jobListing', function ($query) use ($user) {
            $query->where('employer_id', $user->employer->id);
        })->with(['jobListing','jobSeeker'])->get();
    
        return Inertia::render('Applications/ViewApplication', [
            'applications' => $applications
        ]);
    }
    
    
   
    

    public function apply(Request $request, $jobListing)
    {
        $user = Auth::user();
    
        // Ensure user has a role and it's a Job Seeker
        if (!$user || !$user->role || $user->role->name !== 'Job Seeker') {
            abort(403, 'Unauthorized action.');
        }
    
        // Ensure the user has an associated jobSeeker profile
        $jobSeeker = $user->jobSeeker;
        if (!$jobSeeker) {
            return redirect()->back()->withErrors(['error' => 'Job Seeker profile not found.']);
        }
    
        // Validate input
        $validatedData = $request->validate([
            'resume_text' => 'required|string',
            'cover_letter' => 'required|string',
        ]);
    
        // Check if the user has already applied for this job
        $existingApplication = Application::where('jobListing_id', $jobListing)
            ->where('jobSeeker_id', $jobSeeker->id)
            ->first();

        if ($existingApplication) {
            if ($existingApplication->application_status === 'rejected') {
                // If the application was rejected, allow reapplying by updating it
                $existingApplication->update([
                    'resume_text' => $validatedData['resume_text'],
                    'cover_letter' => $validatedData['cover_letter'],
                    'application_status' => 'pending', // Change status to pending
                ]);
    
                return redirect()->route('Jobs')->with('success', 'Application resubmitted successfully.');
            } else {
                // If the application is not rejected, prevent multiple applications
                return redirect()->back()->withErrors(['error' => 'You have already applied for this job.']);
            }
        }
    
        // If no previous application exists, create a new one
        Application::create([
            'jobListing_id' => intval($jobListing), // Ensure it's an integer
            'jobSeeker_id' => $jobSeeker->id,
            'resume_text' => $validatedData['resume_text'],
            'cover_letter' => $validatedData['cover_letter'],
            'application_status' => 'Pending', // Default application status
        ]);
    
        return redirect()->route('Jobs')->with('success', 'Application submitted successfully.');
    }
    
}
