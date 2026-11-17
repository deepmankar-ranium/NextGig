<?php

namespace App\Http\Controllers;

use App\AI\AiException;
use App\Matching\MatchExplainer;
use App\Matching\MatchService;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Actions\Resume\UpdateProfileAction;
use App\Services\JobListingService;

/**
 * A seeker's own match for a job. No seeker id in the URL: you can only see your own data.
 */
class JobMatchController extends Controller
{
    public function explain(Request $request, JobListing $jobListing, MatchService $matches, MatchExplainer $explainer): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role?->name === 'Job Seeker' && $user->jobseeker, 403);

        $seeker = $user->jobseeker;
        $match = $matches->get($seeker, $jobListing->load('tags'));

        if ($match->score === null) {
            return response()->json([
                'score' => null,
                'matched' => $match->matched,
                'missing' => $match->missing,
                'explanation' => ['summary' => $explainer->templateSummary($match), 'tips' => [], 'source' => 'template'],
            ]);
        }

        try {
            $explanation = $explainer->explain(
                $match,
                $jobListing,
                $seeker->profileSkills(),
                isset($seeker->profile['years_experience']) ? (float) $seeker->profile['years_experience'] : null,
            );
        } catch (AiException $e) {
            // The score and skill lists come from code, so the user still gets a useful answer.
            return response()->json([
                'score' => $match->score,
                'matched' => $match->matched,
                'missing' => $match->missing,
                'explanation' => $explainer->templateExplanation($match),
                'notice' => 'The AI is unavailable right now, so this is the plain summary.',
            ]);
        }

        return response()->json([
            'score' => $match->score,
            'matched' => $match->matched,
            'missing' => $match->missing,
            'explanation' => $explanation,
        ]);
    }

    /**
     * "I have this skill": add one of the job's missing skills to the seeker's profile, then
     * return the recomputed match for this job (other jobs are recomputed in the background).
     */
    public function addSkill(Request $request, JobListing $jobListing, MatchService $matches, UpdateProfileAction $update, JobListingService $jobs): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role?->name === 'Job Seeker' && $user->jobseeker?->profile, 403);

        $seeker = $user->jobseeker;
        $missing = $matches->get($seeker, $jobListing->load('tags'))->missing;

        $data = $request->validate(['skill' => ['required', 'string', Rule::in($missing)]]);

        $profile = $seeker->profile;
        $update->execute($seeker, [
            'skills' => [...($profile['skills'] ?? []), $data['skill']],
            'years_experience' => $profile['years_experience'] ?? 0,
            'roles' => $profile['roles'] ?? [],
            'summary' => $profile['summary'] ?? null,
        ]);

        $matches->compute($seeker->refresh(), $jobListing);

        return response()->json(['match' => $jobs->matchPayload($seeker, $jobListing)]);
    }
}
