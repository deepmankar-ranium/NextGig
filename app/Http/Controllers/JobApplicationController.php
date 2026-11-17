<?php

namespace App\Http\Controllers;

use App\Actions\JobApplication\ApplyForJobAction;
use App\Actions\JobApplication\UpdateApplicationStatusAction;
use App\Http\Requests\ApplyForJobRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Models\Application;
use App\Models\JobListing;
use App\Services\JobApplicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobApplicationController extends Controller
{
    public function __construct(private JobApplicationService $jobApplicationService)
    {
    }

    public function show(Request $request)
    {
        $this->authorize('viewAny', Application::class);

        $ranked = $request->query('ranked', '1') !== '0';

        return Inertia::render('Applications/ViewApplication', [
            'applications' => $this->jobApplicationService->getApplicationsForEmployer($request->user(), $ranked),
            'ranked' => $ranked,
        ]);
    }

    public function apply(ApplyForJobRequest $request, JobListing $jobListing, ApplyForJobAction $action)
    {
        $result = $action->execute($jobListing, $request->validated());

        if (isset($result['error'])) {
            return redirect()->back()->withErrors($result);
        }

        return redirect()->route('Jobs')->with($result);
    }

    public function update(UpdateApplicationStatusRequest $request, Application $application, UpdateApplicationStatusAction $action)
    {
        $action->execute($application, $request->validated('application_status'), $request->user(), $request->boolean('reopen'));

        return redirect()->back()->with('success', 'Application status updated successfully.');
    }

    /**
     * The employer opened an application (resume / cover letter): record "viewed" once.
     */
    public function markViewed(Request $request, Application $application, UpdateApplicationStatusAction $action)
    {
        abort_unless($request->user()->can('update', $application), 403);

        return response()->json(['viewed' => $action->markViewed($application, $request->user())]);
    }
}
