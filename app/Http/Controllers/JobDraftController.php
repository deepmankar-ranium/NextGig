<?php

namespace App\Http\Controllers;

use App\AI\AiException;
use App\Employer\JobDescriptionWriter;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobDraftController extends Controller
{
    /**
     * Returns an AI draft for the create form. Never creates or publishes a job.
     */
    public function store(Request $request, JobDescriptionWriter $writer): JsonResponse
    {
        $this->authorize('create', JobListing::class);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'required|string|max:3000',
            'min_years_experience' => 'nullable|integer|min:0|max:40',
        ]);

        try {
            $draft = $writer->draft($data['title'], $data['notes'], $data['min_years_experience'] ?? null);
        } catch (AiException $e) {
            return response()->json(['status' => 'error', 'message' => $e->userMessage()], 503);
        }

        return response()->json(['status' => 'success', 'draft' => $draft]);
    }
}
