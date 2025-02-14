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

        // Create application
        Application::create([
            'jobListing_id' => intval($jobListing), // Ensure it's an integer
            'jobSeeker_id' => $jobSeeker->id,
            'resume_text' => $validatedData['resume_text'],
            'cover_letter' => $validatedData['cover_letter'],
        ]);
        

        return redirect()->route('Jobs')->with('success', 'Application submitted successfully.');
    }
}
