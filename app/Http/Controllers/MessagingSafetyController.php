<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Block / unblock / report from the inbox. Blocking stops messages both ways; the person
 * isn't told. A report is stored for an admin to review and blocks the person by default.
 */
class MessagingSafetyController extends Controller
{
    public function block(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422);

        UserBlock::firstOrCreate(['blocker_id' => $request->user()->id, 'blocked_id' => $user->id]);

        return back()->with('success', "You blocked {$user->full_name}. They can't message you.");
    }

    public function unblock(Request $request, User $user): RedirectResponse
    {
        UserBlock::where('blocker_id', $request->user()->id)->where('blocked_id', $user->id)->delete();

        return back()->with('success', "You unblocked {$user->full_name}.");
    }

    public function report(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();
        abort_if($user->is($me), 422);

        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(UserReport::REASONS))],
            'details' => ['nullable', 'string', 'max:2000'],
            'block' => ['boolean'],
        ]);

        $report = UserReport::create([
            'reporter_id' => $me->id,
            'reported_id' => $user->id,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
        ]);

        Log::warning('messaging.report', ['report_id' => $report->id, 'reported_id' => $user->id, 'reason' => $report->reason]);

        if ($request->boolean('block', true)) {
            UserBlock::firstOrCreate(['blocker_id' => $me->id, 'blocked_id' => $user->id]);
        }

        return back()->with('success', 'Thanks, we\'ll review your report.'.($request->boolean('block', true) ? " {$user->full_name} is blocked." : ''));
    }
}
