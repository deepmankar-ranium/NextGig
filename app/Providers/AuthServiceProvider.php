<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\Tag;
use App\Policies\JobApplicationPolicy;
use App\Policies\JobPolicy;
use App\Policies\TagPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        JobListing::class => JobPolicy::class,
        Application::class => JobApplicationPolicy::class,
        Tag::class => TagPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
