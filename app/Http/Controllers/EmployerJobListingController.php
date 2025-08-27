<?php
namespace App\Http\Controllers;

use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\PostedJobService;
use App\Models\User;

class EmployerJobListingController extends Controller
{
    public function show(PostedJobService $postedJobService)
    {

        $user = Auth::user();

        $isEmployer = $user->isEmployer();

        if (!$isEmployer) {
            abort(403, 'Unauthorized action.');
        }

        $jobListings = $postedJobService->getPostedJob($user->employer->id);

        return Inertia::render('PostedJobs', [
            'jobListings' => $jobListings
        ]);
    }
}


