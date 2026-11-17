<?php

namespace App\Services;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\JobMatch;
use App\Models\User;

/**
 * Data for the role-specific Home dashboard. Everything comes from cached data; no AI calls.
 */
class DashboardService
{
    private const ACTIVE = [Application::STATUS_APPLIED, Application::STATUS_VIEWED, Application::STATUS_SHORTLISTED, Application::STATUS_INTERVIEW];

    public function forSeeker(User $user): array
    {
        $seeker = $user->jobseeker;

        $applications = Application::where('jobSeeker_id', $seeker->id)
            ->with('jobListing.employer:id,name')
            ->latest('updated_at')
            ->get();

        $appliedJobIds = $applications->pluck('jobListing_id');

        $topMatches = JobMatch::where('jobseeker_id', $seeker->id)
            ->whereNotNull('score')
            ->whereNotIn('job_listing_id', $appliedJobIds)
            ->whereHas('jobListing')
            ->with('jobListing.employer:id,name')
            ->orderByDesc('score')
            ->limit(4)
            ->get()
            ->map(fn (JobMatch $m) => [
                'id' => $m->jobListing->id,
                'title' => $m->jobListing->title,
                'employer' => $m->jobListing->employer?->name,
                'score' => $m->score,
                'missing' => array_slice($m->missing, 0, 3),
            ]);

        return [
            'role' => 'seeker',
            'name' => $user->full_name,
            'hasProfile' => filled($seeker->profile),
            'skillCount' => count($seeker->profileSkills()),
            'topMatches' => $topMatches,
            'counts' => [
                'active' => $applications->whereIn('application_status', self::ACTIVE)->count(),
                'interviews' => $applications->where('application_status', Application::STATUS_INTERVIEW)->count(),
                'offers' => $applications->where('application_status', Application::STATUS_OFFER)->count(),
            ],
            'recentApplications' => $applications->take(4)->map(fn (Application $a) => [
                'id' => $a->id,
                'job_id' => $a->jobListing_id,
                'title' => $a->jobListing?->title,
                'employer' => $a->jobListing?->employer?->name,
                'status' => $a->application_status,
                'updated_at' => $a->updated_at?->toIso8601String(),
            ])->values(),
        ];
    }

    public function forEmployer(User $user): array
    {
        $employerId = $user->employer->id;

        $jobs = JobListing::where('employer_id', $employerId)
            ->withCount([
                'applications',
                'applications as new_count' => fn ($q) => $q->where('application_status', Application::STATUS_APPLIED),
            ])
            ->latest()
            ->get();

        $applications = Application::whereIn('jobListing_id', $jobs->pluck('id'))
            ->with(['jobListing:id,title', 'jobSeeker:id,name'])
            ->latest()
            ->limit(5)
            ->get();

        $scores = JobMatch::whereIn('job_listing_id', $applications->pluck('jobListing_id'))
            ->whereIn('jobseeker_id', $applications->pluck('jobSeeker_id'))
            ->get()
            ->keyBy(fn (JobMatch $m) => $m->job_listing_id.'-'.$m->jobseeker_id);

        $statusCounts = Application::whereIn('jobListing_id', $jobs->pluck('id'))
            ->selectRaw('application_status, count(*) as total')
            ->groupBy('application_status')
            ->pluck('total', 'application_status');

        return [
            'role' => 'employer',
            'name' => $user->full_name,
            'company' => $user->employer->name,
            'stats' => [
                'roles' => $jobs->count(),
                'applicants' => (int) $statusCounts->sum(),
                'new' => (int) ($statusCounts[Application::STATUS_APPLIED] ?? 0),
                'inProgress' => (int) (($statusCounts[Application::STATUS_SHORTLISTED] ?? 0) + ($statusCounts[Application::STATUS_INTERVIEW] ?? 0)),
            ],
            'recentApplicants' => $applications->map(fn (Application $a) => [
                'id' => $a->id,
                'name' => $a->jobSeeker?->name ?? 'Applicant',
                'job_id' => $a->jobListing_id,
                'job' => $a->jobListing?->title,
                'status' => $a->application_status,
                'score' => $scores->get($a->jobListing_id.'-'.$a->jobSeeker_id)?->score,
                'created_at' => $a->created_at?->toIso8601String(),
            ]),
            'roles' => $jobs->take(6)->map(fn (JobListing $j) => [
                'id' => $j->id,
                'title' => $j->title,
                'applicants' => $j->applications_count,
                'new' => $j->new_count,
                'created_at' => $j->created_at?->toIso8601String(),
            ])->values(),
        ];
    }
}
