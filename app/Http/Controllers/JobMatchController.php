<?php

namespace App\Http\Controllers;

use App\AI\AiException;
use App\Matching\MatchExplainer;
use App\Matching\MatchService;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            return response()->json(['status' => 'error', 'message' => $e->userMessage()], 503);
        }

        return response()->json([
            'score' => $match->score,
            'matched' => $match->matched,
            'missing' => $match->missing,
            'explanation' => $explanation,
        ]);
    }
}
