<?php

namespace Database\Seeders;

use App\Matching\MatchService;
use App\Models\Application;
use App\Models\DirectMessage;
use App\Models\Employer;
use App\Models\JobListing;
use App\Models\Jobseeker;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

/**
 * Small, realistic demo dataset with two log-in accounts (see README):
 *   employer@nextgig.test / password
 *   seeker@nextgig.test   / password
 * No AI calls: profiles are pre-filled, scores are computed by code.
 */
class DemoSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public function run(): void
    {
        // No welcome emails, broadcasts or queued jobs while seeding.
        Event::fake();

        foreach ([1 => 'Admin', 2 => 'Employer', 3 => 'Job Seeker'] as $id => $name) {
            Role::firstOrCreate(['id' => $id], ['name' => $name]);
        }

        $employerUser = $this->user('Priya Nair', 'employer@nextgig.test', 2);
        $employer = Employer::firstOrCreate(['user_id' => $employerUser->id], [
            'name' => 'Brightline Labs',
            'email' => 'jobs@brightline.test',
            'address' => 'Pune, India',
            'description' => 'A product studio building tools for small businesses.',
        ]);

        $otherEmployerUser = $this->user('Arjun Mehta', 'hiring@northwind.test', 2);
        $otherEmployer = Employer::firstOrCreate(['user_id' => $otherEmployerUser->id], [
            'name' => 'Northwind Logistics',
            'email' => 'careers@northwind.test',
            'address' => 'Bengaluru, India',
        ]);

        $tags = collect(['php', 'laravel', 'vue.js', 'mysql', 'docker', 'aws', 'react', 'typescript', 'node.js', 'python', 'rest api', 'tailwind css'])
            ->mapWithKeys(fn ($name) => [$name => Tag::firstOrCreate(['name' => $name], ['created_by' => $employerUser->id])]);

        $jobs = [
            [$employer, 'Senior Laravel Developer', 1800000, 4, ['php', 'laravel', 'mysql', 'docker', 'rest api'],
                "Own the backend of our invoicing product.\n\nResponsibilities:\n- Design and build Laravel APIs\n- Improve query performance on MySQL\n- Review code and mentor two junior developers\n\nRequirements:\n- 4+ years with PHP and Laravel\n- Comfortable with Docker-based workflows"],
            [$employer, 'Full-stack Developer (Laravel + Vue)', 1400000, 2, ['php', 'laravel', 'vue.js', 'tailwind css'],
                "Ship features end to end across our Inertia + Vue frontend and Laravel backend.\n\nRequirements:\n- 2+ years building web apps\n- Vue 3 and Tailwind CSS"],
            [$employer, 'Frontend Engineer', 1300000, null, ['vue.js', 'typescript', 'tailwind css', 'react'],
                'Build accessible, fast interfaces for thousands of small-business owners.'],
            [$otherEmployer, 'Platform Engineer', 2200000, 5, ['aws', 'docker', 'python', 'node.js'],
                'Run the infrastructure behind our route-planning platform.'],
            [$otherEmployer, 'Backend Developer (Node.js)', 1500000, 3, ['node.js', 'typescript', 'rest api', 'mysql'],
                'Build the APIs our drivers and dispatchers rely on every day.'],
        ];

        $listings = collect($jobs)->map(function ($j) use ($tags) {
            [$emp, $title, $salary, $years, $skills, $description] = $j;
            $job = JobListing::firstOrCreate(['employer_id' => $emp->id, 'title' => $title], [
                'description' => $description,
                'salary' => $salary,
                'min_years_experience' => $years,
            ]);
            $job->tags()->sync(collect($skills)->map(fn ($s) => $tags[$s]->id));

            return $job;
        });

        $seekerUser = $this->user('Asha Rao', 'seeker@nextgig.test', 3);
        $seeker = $this->seeker($seekerUser, [
            'skills' => ['php', 'laravel', 'vue.js', 'mysql', 'tailwind css', 'git'],
            'years_experience' => 3,
            'roles' => ['Laravel Developer', 'Junior Web Developer'],
            'summary' => 'Web developer with three years of Laravel and Vue experience building internal tools.',
        ]);

        $applicants = [
            ['Rohan Das', 'rohan@example.test', ['php', 'laravel', 'mysql', 'docker', 'rest api', 'aws'], 6],
            ['Meera Iyer', 'meera@example.test', ['php', 'laravel', 'vue.js'], 2],
            ['Kabir Singh', 'kabir@example.test', ['python', 'django', 'postgresql'], 4],
        ];

        $matches = app(MatchService::class);

        // The demo seeker's own applications, at different stages.
        $this->apply($seeker, $listings[0], [Application::STATUS_VIEWED, Application::STATUS_SHORTLISTED], $employerUser);
        $this->apply($seeker, $listings[1], [Application::STATUS_VIEWED], $employerUser);
        $this->apply($seeker, $listings[4], [Application::STATUS_REJECTED], $otherEmployerUser);

        // Other applicants to the demo employer's senior role, so ranking has something to show.
        foreach ($applicants as [$name, $email, $skills, $years]) {
            $s = $this->seeker($this->user($name, $email, 3), [
                'skills' => $skills, 'years_experience' => $years, 'roles' => [], 'summary' => '',
            ]);
            $this->apply($s, $listings[0], [], $employerUser);
        }

        Jobseeker::all()->each(fn (Jobseeker $s) => $matches->recomputeForSeeker($s));

        $this->conversation($employerUser, $seekerUser, [
            [0, 'Hi Asha, thanks for applying to the Senior Laravel Developer role. Your Laravel work stood out.', 60 * 26],
            [1, 'Thank you! I\'m really interested in the invoicing product.', 60 * 25],
            [0, 'Great. Would you be free for a 30-minute call on Tuesday or Wednesday?', 45],
            [1, 'Wednesday afternoon works well for me.', 30],
            [0, 'Perfect, I\'ll send an invite for 3pm. Talk soon!', 5],
        ]);
        $this->conversation($otherEmployerUser, $seekerUser, [
            [0, 'Hello! We\'ve reviewed your application for the Node.js role.', 60 * 24 * 3],
        ]);
    }

    /**
     * @param  list<array{0: int, 1: string, 2: int}>  $lines  [0 = first user speaks / 1 = second, text, minutes ago]
     */
    private function conversation(User $a, User $b, array $lines): void
    {
        if (DirectMessage::between($a->id, $b->id)->exists()) {
            return;
        }

        foreach ($lines as [$who, $text, $minutesAgo]) {
            $m = new DirectMessage([
                'sender_id' => $who === 0 ? $a->id : $b->id,
                'receiver_id' => $who === 0 ? $b->id : $a->id,
                'message' => $text,
            ]);
            $m->created_at = $m->updated_at = now()->subMinutes($minutesAgo);
            $m->save();
        }

        // Everything except the newest message has been read.
        DirectMessage::between($a->id, $b->id)->where('created_at', '<', now()->subMinutes(10))->update(['read_at' => now()]);
    }

    private function user(string $name, string $email, int $roleId): User
    {
        return User::firstOrCreate(['email' => $email], [
            'full_name' => $name,
            'password' => Hash::make(self::PASSWORD),
            'role_id' => $roleId,
            'email_verified_at' => now(),
        ]);
    }

    private function seeker(User $user, array $profile): Jobseeker
    {
        return Jobseeker::updateOrCreate(['user_id' => $user->id], [
            'name' => $user->full_name,
            'email' => $user->email,
            'skills' => implode(', ', $profile['skills']),
            'profile' => $profile,
            'profile_updated_at' => now(),
        ]);
    }

    /**
     * @param  list<string>  $stages  stages after "applied", in order
     */
    private function apply(Jobseeker $seeker, JobListing $job, array $stages, User $employer): void
    {
        if (Application::where('jobSeeker_id', $seeker->id)->where('jobListing_id', $job->id)->exists()) {
            return;
        }

        $application = new Application([
            'jobListing_id' => $job->id,
            'jobSeeker_id' => $seeker->id,
            'resume_text' => "{$seeker->name}\n".implode(', ', $seeker->profile['skills'] ?? []),
            'cover_letter' => "Hello! I'd love to join the team as {$job->title}.",
        ]);
        $application->transitionTo(Application::STATUS_APPLIED, $seeker->user);

        foreach ($stages as $stage) {
            $application->transitionTo($stage, $employer);
        }
    }
}
