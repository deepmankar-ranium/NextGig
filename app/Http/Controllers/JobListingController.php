<?php

namespace App\Http\Controllers;

use App\Actions\JobListing\CreateJobListingAction;
use App\Actions\JobListing\DeleteJobListingAction;
use App\Actions\JobListing\UpdateJobListingAction;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Models\JobListing;
use App\Services\JobListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobListingController extends Controller
{
    public function __construct(private JobListingService $jobListingService)
    {
    }

    public function index(Request $request)
    {
        $jobListings = $this->jobListingService->getJobListings($request->all());
        return Inertia::render('Welcome', [
            'jobListings' => $jobListings,
        ]);
    }

    public function jobs(Request $request)
    {
        $jobListings = $this->jobListingService->getJobListings($request->all());
        $tags = $this->jobListingService->getTags();
        $isEmployer = auth()->user()?->role->name === 'Employer';

        return Inertia::render('Jobs', [
            'jobListings' => $jobListings,
            'isEmployer' => $isEmployer,
            'tags' => $tags,
        ]);
    }

    public function filterJobs(Request $request)
    {
        $jobListings = $this->jobListingService->getJobListings($request->all());
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
        return redirect('/Jobs')->with('success', 'Job listing created successfully!');
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
        return redirect("/Jobs/job/{$jobListing->id}")->with('success', 'Job listing updated successfully!');
    }

    public function destroy(JobListing $jobListing, DeleteJobListingAction $action)
    {
        $action->execute($jobListing);
        return redirect("/Jobs")->with('success', 'Job deleted successfully!');
    }

    public function search(Request $request)
    {
        $searchResults = $this->jobListingService->getJobListings($request->all());
        return response()->json(['searchResults' => $searchResults]);
    }
}
