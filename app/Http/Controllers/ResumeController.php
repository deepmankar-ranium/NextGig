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
        $seeker = $request->user()->jobseeker;
        $before = $seeker->profileSkills();

        try {
            $action->execute($seeker, $request->file('resume'));
        } catch (ResumeException|AiException $e) {
            return back()->withErrors(['resume' => $e->userMessage()]);
        }

        return redirect('/resume')->with('status', self::parseSummary($before, $seeker->fresh()->profile ?? []));
    }

    public function update(UpdateProfileRequest $request, UpdateProfileAction $action)
    {
        $action->execute($request->user()->jobseeker, $request->validated());

        return redirect('/resume')->with('status', 'Profile saved.');
    }

    /**
     * Tell the user what the parser found, so a poor parse (scanned PDF, odd layout) is noticed.
     */
    public static function parseSummary(array $skillsBefore, array $profile): string
    {
        $skills = $profile['skills'] ?? [];
        $new = array_values(array_diff($skills, $skillsBefore));
        $years = $profile['years_experience'] ?? null;
        $roles = count($profile['roles'] ?? []);

        $parts = [count($skills).' '.str('skill')->plural(count($skills))];
        if ($years !== null) {
            $parts[] = rtrim(rtrim(number_format((float) $years, 1), '0'), '.').' '.str('year')->plural((float) $years === 1.0 ? 1 : 2).' of experience';
        }
        $parts[] = $roles.' '.str('role')->plural($roles);

        $message = 'Resume read: found '.implode(', ', $parts).'.';

        if ($skillsBefore && $new) {
            $message .= ' New: '.implode(', ', array_slice($new, 0, 5)).(count($new) > 5 ? '…' : '').'.';
        }

        if (count($skills) < 3) {
            $message .= " That's fewer than expected. If your PDF is a scan or has an unusual layout, add your skills below.";
        } else {
            $message .= ' Check them below and fix anything that\'s wrong.';
        }

        return $message;
    }
}
