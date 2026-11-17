<?php

namespace App\Http\Controllers;

use App\Actions\JobListing\CreateJobListingAction;
use App\Actions\JobListing\DeleteJobListingAction;
use App\Actions\JobListing\UpdateJobListingAction;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Models\JobListing;
use App\Services\DashboardService;
use App\Services\JobListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobListingController extends Controller
{
    public function __construct(private JobListingService $jobListingService)
    {
    }

    private function seeker(Request $request)
    {
        $user = $request->user();

        return $user?->role?->name === 'Job Seeker' ? $user->jobseeker : null;
    }

    /**
     * Home: a role-specific dashboard.
     */
    public function index(Request $request, DashboardService $dashboard)
    {
        $user = $request->user();

        $data = match (true) {
            $user->role?->name === 'Employer' && $user->employer !== null => $dashboard->forEmployer($user),
            $user->role?->name === 'Job Seeker' && $user->jobseeker !== null => $dashboard->forSeeker($user),
            default => ['role' => 'other', 'name' => $user->full_name],
        };

        return Inertia::render('Dashboard', $data);
    }

    public function jobs(Request $request)
    {
        $jobListings = $this->jobListingService->getJobListings($request->all(), $this->seeker($request));
        $tags = $this->jobListingService->getTags();
        $isEmployer = auth()->user()?->role->name === 'Employer';

        return Inertia::render('Jobs', [
            'jobListings' => $jobListings,
            'isEmployer' => $isEmployer,
            'isJobSeeker' => $this->seeker($request) !== null,
            'tags' => $tags,
        ]);
    }

    public function filterJobs(Request $request)
    {
        $jobListings = $this->jobListingService->getJobListings($request->all(), $this->seeker($request));
        return response()->json([
            'jobListings' => $jobListings
        ]);
    }

    public function show(JobListing $jobListing)
    {
        $data = $this->jobListingService->getJobDetails($jobListing);
        return Inertia::render('JobDetails', $data);
    }

    public function create()
    {
        $this->authorize('create', JobListing::class);
        return Inertia::render('Jobs/Create', [
            'employer' => auth()->user()->employer,
            'tags' => $this->jobListingService->getTags(),
        ]);
    }

    public function store(StoreJobListingRequest $request, CreateJobListingAction $action)
    {
        $action->execute($request->validated());
        return redirect('/jobs')->with('success', 'Job listing created successfully!');
    }

    public function edit(JobListing $jobListing)
    {
        $this->authorize('update', $jobListing);
        return Inertia::render('Jobs/Edit', [
            'job' => $jobListing->load(['employer', 'tags']),
            'tags' => $this->jobListingService->getTags(),
        ]);
    }

    public function update(UpdateJobListingRequest $request, JobListing $jobListing, UpdateJobListingAction $action)
    {
        $action->execute($jobListing, $request->validated());
        return redirect("/jobs/{$jobListing->id}")->with('success', 'Job listing updated successfully!');
    }

    public function destroy(JobListing $jobListing, DeleteJobListingAction $action)
    {
        $action->execute($jobListing);
        return redirect("/jobs")->with('success', 'Job deleted successfully!');
    }

    public function search(Request $request)
    {
        $searchResults = $this->jobListingService->getJobListings($request->all(), $this->seeker($request));
        return response()->json(['searchResults' => $searchResults]);
    }
}
