<?php

namespace App\Services;

use App\Matching\MatchService;
use App\Models\Application;
use App\Models\ApplicationStatusChange;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class JobApplicationService
{
    public function __construct(private readonly MatchService $matches)
    {
    }

    /**
     * Applications to the employer's own jobs. When $ranked, sorted by the deterministic match
     * score (M3) — computed from skills and years only; name, email, photo, gender, age and
     * location are never inputs. Ranking only orders the list; it never changes a status.
     */
    public function getApplicationsForEmployer(User $employer, bool $ranked = true): Collection
    {
        $applications = Application::whereHas('jobListing', fn ($q) => $q->where('employer_id', $employer->employer->id))
            ->with(['jobListing.tags', 'jobSeeker', 'statusHistory.actor:id,full_name'])
            ->latest()
            ->get();

        $rows = $applications->map(function (Application $application) {
            $match = $application->jobSeeker
                ? $this->matches->get($application->jobSeeker, $application->jobListing)
                : null;

            return [
                ...Arr::except($application->toArray(), ['status_history']),
                'match' => $match ? [
                    'score' => $match->score,
                    'matched' => $match->matched,
                    'missing' => $match->missing,
                ] : null,
                'history' => $application->statusHistory->map(fn (ApplicationStatusChange $c) => [
                    'from' => $c->from_status,
                    'to' => $c->to_status,
                    'by' => $c->actor?->full_name ?? 'System',
                    'at' => $c->created_at?->toIso8601String(),
                ])->all(),
            ];
        });

        if ($ranked) {
            // Stable sort (PHP 8): higher score first, unscored last, ties stay newest first.
            $rows = $rows->sortByDesc(fn ($row) => $row['match']['score'] ?? -1);
        }

        return $rows->values();
    }
}
