<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
class JobListingController extends Controller
{
    public function index()
    {
        $jobListings = JobListing::with('employer')->paginate(6);
        return Inertia::render('Welcome', [
            'jobListings' => $jobListings,
        ]);
    }

    public function jobs()
    {
        $jobListings = JobListing::with('employer')->latest()->paginate(6);
        return Inertia::render('Jobs', [
            'jobListings' => $jobListings,
        ]);
    }

    public function show($id)
    {
        $job = JobListing::findOrFail($id);
        return Inertia::render('JobDetails', [
            'job' => $job,
        ]);
    }

    public function create()
    {
        return Inertia::render('Jobs/Create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'employer_id' => 'required|exists:employers,id',
        ]);

        JobListing::create($validatedData);

        return redirect('/Jobs')->with('success', 'Job listing created successfully!');
    }

  public function edit($id)
{
    $job = JobListing::findOrFail($id);

    if ($job->employer->user->isNot(Auth::user())) {
        abort(403);
    }

    return Inertia::render('Jobs/Edit', compact('job'));
}

    public function update(Request $request, $id)
    {
        $job = JobListing::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'employer_id' => 'required|exists:employers,id',
        ]);

        $job->update($validatedData);

        return redirect("/Jobs/job/{$id}")->with('success', 'Job listing updated successfully!');
    }

    public function destroy($id)
    {
        $job = JobListing::findOrFail($id);
        $job->delete();

        return redirect("/Jobs")->with('success', 'Job deleted successfully!');
    }
}
