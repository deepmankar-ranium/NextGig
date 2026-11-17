<?php

namespace App\Actions\Auth;

use App\Models\Employer;
use App\Models\Jobseeker;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Outcomes:
 *  - 'logged_in'   existing account (by Google id or email) or a new one created with the role
 *                  the visitor already picked on /register
 *  - 'needs_role'  new visitor who hasn't picked Employer / Job seeker yet; their Google profile
 *                  is kept in the session and the account is created once they pick (see
 *                  completePendingSignup)
 *  - 'failed'      Google returned an error or the OAuth state didn't match
 */
class HandleGoogleCallbackAction
{
    public const SESSION_KEY = 'google_pending';

    public function execute(): string
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            // Log the type only: never tokens or profile data.
            Log::warning('auth.google.failed', ['exception' => $e::class]);

            return 'failed';
        }

        if (blank($google->getEmail())) {
            return 'failed';
        }

        $user = User::where('google_id', $google->getId())->first()
            ?? User::where('email', $google->getEmail())->first();

        if ($user) {
            if (blank($user->google_id)) {
                $user->forceFill(['google_id' => $google->getId()])->save();
            }
            $this->login($user);

            return 'logged_in';
        }

        $profile = ['id' => $google->getId(), 'name' => $google->getName() ?: $google->getEmail(), 'email' => $google->getEmail()];
        $roleId = (int) session('user_role');

        if (! in_array($roleId, [2, 3], true)) {
            session([self::SESSION_KEY => $profile]);

            return 'needs_role';
        }

        $this->login($this->createUser($profile, $roleId));

        return 'logged_in';
    }

    /**
     * Called after a Google visitor picks a role on /register.
     */
    public function completePendingSignup(int $roleId): ?User
    {
        $profile = session()->pull(self::SESSION_KEY);

        if (! $profile || ! in_array($roleId, [2, 3], true)) {
            return null;
        }

        $user = User::where('email', $profile['email'])->first() ?? $this->createUser($profile, $roleId);
        $this->login($user);

        return $user;
    }

    private function createUser(array $profile, int $roleId): User
    {
        return DB::transaction(function () use ($profile, $roleId) {
            $user = User::create([
                'full_name' => $profile['name'],
                'email' => $profile['email'],
                'password' => bcrypt(str()->random(40)),
                'role_id' => $roleId,
                'google_id' => $profile['id'],
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            if ($roleId === 2) {
                Employer::create(['user_id' => $user->id, 'name' => $profile['name'], 'email' => $profile['email'], 'address' => 'Not Provided']);
            } else {
                Jobseeker::create(['user_id' => $user->id, 'name' => $profile['name'], 'email' => $profile['email']]);
            }

            return $user;
        });
    }

    private function login(User $user): void
    {
        Auth::login($user, remember: true);
        session()->regenerate();
        session()->forget('user_role');
    }
}
