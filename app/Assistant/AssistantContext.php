<?php

namespace App\Assistant;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\JobMatch;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * What the career assistant knows about the user, as plain data for the system prompt.
 * Seekers: their parsed profile, recent applications (stage + match) and best matches.
 * Employers: their postings with applicant counts per stage. Applicants' names, emails and
 * resumes are never included.
 */
class AssistantContext
{
    private const MAX_ITEMS = 8;

    /** Jobs the user may focus the assistant on: any job for seekers, own jobs for employers. */
    public function canFocus(User $user, JobListing $job): bool
    {
        if ($user->employer) {
            return $job->employer_id === $user->employer->id;
        }

        return (bool) $user->jobseeker;
    }

    public function build(User $user, ?JobListing $focus = null): array
    {
        $context = $user->employer ? $this->employer($user) : $this->seeker($user);

        if ($focus) {
            $context['focus_job'] = $this->job($focus, $user);
        }

        return $context;
    }

    private function seeker(User $user): array
    {
        $seeker = $user->jobseeker;
        if (! $seeker) {
            return ['role' => 'job seeker'];
        }

        $profile = $seeker->profile ?? [];

        $applications = Application::where('jobSeeker_id', $seeker->id)
            ->with('jobListing.tags')
            ->latest('id')
            ->limit(self::MAX_ITEMS)
            ->get()
            ->filter(fn ($a) => $a->jobListing)
            ->map(function (Application $a) use ($seeker) {
                $match = JobMatch::where('jobseeker_id', $seeker->id)->where('job_listing_id', $a->jobListing->id)->first();

                return [
                    'job_title' => $a->jobListing->title,
                    'stage' => $a->application_status,
                    'applied_on' => $a->created_at?->toDateString(),
                    'match_score' => $match?->score,
                    'missing_skills' => $match?->missing ?? [],
                ];
            })->values()->all();

        $appliedJobIds = Application::where('jobSeeker_id', $seeker->id)->pluck('jobListing_id');

        $bestMatches = JobMatch::where('jobseeker_id', $seeker->id)
            ->whereNotNull('score')
            ->whereNotIn('job_listing_id', $appliedJobIds)
            ->orderByDesc('score')
            ->with('jobListing:id,title')
            ->limit(5)
            ->get()
            ->filter(fn ($m) => $m->jobListing)
            ->map(fn ($m) => ['job_title' => $m->jobListing->title, 'match_score' => $m->score, 'missing_skills' => $m->missing])
            ->values()->all();

        return [
            'role' => 'job seeker',
            'profile' => [
                'skills' => $profile['skills'] ?? [],
                'roles' => $profile['roles'] ?? [],
                'years_experience' => $profile['years_experience'] ?? null,
            ],
            'has_resume' => (bool) $seeker->resume,
            'applications' => $applications,
            'best_matches_not_applied' => $bestMatches,
        ];
    }

    private function employer(User $user): array
    {
        $jobs = JobListing::where('employer_id', $user->employer->id)
            ->with('tags')
            ->latest('id')
            ->limit(self::MAX_ITEMS)
            ->get()
            ->map(fn (JobListing $job) => $this->job($job, $user, withDescription: false))
            ->all();

        return ['role' => 'employer', 'company' => $user->employer->name, 'job_postings' => $jobs];
    }

    private function job(JobListing $job, User $user, bool $withDescription = true): array
    {
        $job->loadMissing('tags');

        $data = [
            'title' => $job->title,
            'skills' => $job->tags->pluck('name')->all(),
            'min_years_experience' => $job->min_years_experience,
        ];

        if ($withDescription) {
            $data['description'] = Str::limit(strip_tags((string) $job->description), 1500);
        }

        if ($user->employer && $job->employer_id === $user->employer->id) {
            $data['applicants_by_stage'] = Application::where('jobListing_id', $job->id)
                ->selectRaw('application_status, count(*) as n')
                ->groupBy('application_status')
                ->pluck('n', 'application_status')
                ->all();
        } elseif ($user->jobseeker) {
            $match = JobMatch::where('jobseeker_id', $user->jobseeker->id)->where('job_listing_id', $job->id)->first();
            $application = Application::where('jobSeeker_id', $user->jobseeker->id)->where('jobListing_id', $job->id)->first();
            $data['my_match_score'] = $match?->score;
            $data['my_missing_skills'] = $match?->missing ?? [];
            $data['my_application_stage'] = $application?->application_status;
        }

        return $data;
    }
}
