<?php

namespace App\Http\Controllers;

use App\Actions\JobApplication\ApplyForJobAction;
use App\Actions\JobApplication\UpdateApplicationStatusAction;
use App\Http\Requests\ApplyForJobRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Models\Application;
use App\Resume\ResumeException;
use Illuminate\Support\Facades\Storage;
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
        try {
            $result = $action->execute($request->user(), $jobListing, $request->file('resume'), $request->validated('cover_letter'));
        } catch (ResumeException $e) {
            return back()->withErrors(['resume' => $e->userMessage()]);
        }

        if (isset($result['error'])) {
            return redirect()->back()->withErrors($result);
        }

        return redirect()->route('jobs.index')->with($result);
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

    /**
     * The resume PDF submitted with an application. The owning employer opening it counts as
     * "viewed"; the applicant can open their own copy too.
     */
    public function resume(Request $request, Application $application, UpdateApplicationStatusAction $action)
    {
        $user = $request->user();
        $isOwner = $user->can('update', $application);
        abort_unless($isOwner || $application->jobSeeker?->user_id === $user->id, 403);
        abort_unless($application->resume_path && Storage::disk('uploads')->exists($application->resume_path), 404);

        if ($isOwner) {
            $action->markViewed($application, $user);
        }

        $name = str($application->jobSeeker?->name ?? 'applicant')->slug().'-resume.pdf';

        return Storage::disk('uploads')->response($application->resume_path, $name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$name.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
