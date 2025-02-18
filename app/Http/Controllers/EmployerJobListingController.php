<?php
namespace App\Http\Controllers;

use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmployerJobListingController extends Controller
{
    public function show()
    {

        $user = Auth::user();

 
        if (!$user || !$user->role || $user->role->name !== "Employer") {
            abort(403, 'Unauthorized action.');
        }

       
        $employerId = $user->employer->id;  

       

        $jobListings = JobListing::with(['employer'])
            ->where('employer_id', $employerId)  
            ->latest()
            ->paginate(6);

    
        return Inertia::render('PostedJobs', [
            'jobListings' => $jobListings,
            'isEmployer' => true, 
        ]);
    }
}


