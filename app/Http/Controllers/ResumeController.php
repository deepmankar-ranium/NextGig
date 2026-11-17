<?php

namespace App\Http\Controllers;

use App\Actions\Resume\UpdateProfileAction;
use App\Actions\Resume\UploadResumeAction;
use App\AI\AiException;
use App\Http\Requests\Resume\UpdateProfileRequest;
use App\Http\Requests\Resume\UploadResumeRequest;
use App\Resume\ResumeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

/**
 * A seeker's own resume and parsed profile. There are no ids in these routes: you can
 * only ever reach your own data.
 */
class ResumeController extends Controller
{
    public function show(Request $request)
    {
        Gate::authorize('manage-resume');

        $seeker = $request->user()->jobseeker;

        return Inertia::render('Resume/Edit', [
            'hasResume' => filled($seeker->resume),
            'profile' => $seeker->profile,
            'profileUpdatedAt' => $seeker->profile_updated_at,
        ]);
    }

    public function upload(UploadResumeRequest $request, UploadResumeAction $action)
    {
        try {
            $action->execute($request->user()->jobseeker, $request->file('resume'));
        } catch (ResumeException|AiException $e) {
            return back()->withErrors(['resume' => $e->userMessage()]);
        }

        return redirect('/resume')->with('status', 'Resume parsed. Review your profile below.');
    }

    public function update(UpdateProfileRequest $request, UpdateProfileAction $action)
    {
        $action->execute($request->user()->jobseeker, $request->validated());

        return redirect('/resume')->with('status', 'Profile saved.');
    }
}
