<?php
namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Services\EmployerJobListingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmployerJobListingController extends Controller
{
    public function __construct(private EmployerJobListingService $employerJobListingService)
    {
    }

    public function show()
    {
        $this->authorize('viewEmployerJobs', JobListing::class);

        $jobListings = $this->employerJobListingService->getJobListings();

        return Inertia::render('PostedJobs', [
            'jobListings' => $jobListings,
            'isEmployer' => true,
        ]);
    }
}