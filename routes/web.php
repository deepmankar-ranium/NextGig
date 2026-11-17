<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\EmployerJobListingController;
use App\Http\Controllers\AppliedJobsController;
use App\Http\Controllers\ForgetPassword;
use App\Http\Controllers\GoogleAuthController;
use Inertia\Inertia;
use App\Http\Controllers\GPTController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\JobMatchController;
use App\Http\Controllers\JobDraftController;


Route::get('/', function () {
    return Inertia::render('Landing');
});

Route::middleware(['auth'])->group(function () {
    // Job Listing routes
    Route::get('/home', [JobListingController::class, 'index'])->name('home');
    Route::get('/jobs', [JobListingController::class, 'jobs'])->name('jobs.index');
    Route::get('/jobs/create', [JobListingController::class, 'create']);
    Route::post('/jobs/draft', [JobDraftController::class, 'store'])->middleware('throttle:ai')->name('jobs.draft');
    Route::post('/jobs/create', [JobListingController::class, 'store'])->middleware('throttle:writes')->name('jobs.store');
    Route::get('/jobs/{jobListing}', [JobListingController::class, 'show'])->whereNumber('jobListing');
    Route::get('/jobs/{jobListing}/edit', [JobListingController::class, 'edit'])->whereNumber('jobListing');
    Route::patch('/jobs/{jobListing}', [JobListingController::class, 'update'])->whereNumber('jobListing');
    Route::delete('/jobs/{jobListing}', [JobListingController::class, 'destroy'])->whereNumber('jobListing');
    Route::post('/jobs/{jobListing}/match/explain', [JobMatchController::class, 'explain'])->middleware('throttle:ai')->whereNumber('jobListing')->name('jobs.match.explain');
    Route::get('/search', [JobListingController::class, 'search'])->name('search');
    Route::get('/filter', [JobListingController::class, 'filterJobs']);

    // Application routes
    Route::get('/view-applications', [JobApplicationController::class, 'show']);
    Route::post('/apply/{jobListing}', [JobApplicationController::class, 'apply'])->middleware('throttle:writes');
    Route::put('/applications/{application}', [JobApplicationController::class, 'update'])->middleware('throttle:writes');
    Route::post('/applications/{application}/viewed', [JobApplicationController::class, 'markViewed'])->middleware('throttle:writes');
    Route::get('/applications/{application}/resume', [JobApplicationController::class, 'resume'])->name('applications.resume');

    // User-specific job routes
    Route::get('/posted-jobs', [EmployerJobListingController::class, 'show']);
    Route::get('/applied-jobs', [AppliedJobsController::class, 'show']);

    // Chat routes
    Route::get('/chat', [GPTController::class, 'getChat']);
    Route::get('/chat-history', [GPTController::class, 'getChatHistory']);
    Route::middleware('throttle:ai')->group(function () {
        Route::post('/chat', [GPTController::class, 'chat'])->name('chat');
        Route::post('/clear-chat', [GPTController::class, 'clearChat']);
        Route::post('/create-intro-message', [GPTController::class, 'createIntroMessage'])->name('chat.intro');
    });


    // Inbox chat routes
    Route::get('/messages/{user?}', [MessagesController::class, 'show'])->name('messages.show');
    Route::post('/messages', [MessagesController::class, 'store'])->middleware('throttle:writes')->name('messages.store');
    Route::post('/messages/delete/{sender_id}/{receiver_id}', [MessagesController::class, 'deleteMessages'])->middleware('throttle:writes')->name('messages.delete');
    Route::delete('/messages/{message_id}', [MessagesController::class, 'destroy'])->middleware('throttle:writes')->name('message.destroy');

    // Tag routes
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/create-tags', [TagController::class, 'store'])->middleware('throttle:writes');
    Route::post('/delete-tags', [TagController::class, 'destroy'])->middleware('throttle:writes');

    // Profile routes
    Route::get('/profile', [ProfileController::class,'profile']);

    // Resume + parsed profile (job seekers, own data only)
    Route::get('/resume', [ResumeController::class, 'show'])->name('resume.show');
    Route::post('/resume', [ResumeController::class, 'upload'])->middleware('throttle:ai')->name('resume.upload');
    Route::put('/resume/profile', [ResumeController::class, 'update'])->middleware('throttle:writes')->name('resume.profile');
});

// Old capitalised URLs (before 2026-11) → permanent redirects, so bookmarks and emails keep working.
Route::permanentRedirect('/Home', '/home');
Route::permanentRedirect('/Jobs', '/jobs');
Route::permanentRedirect('/Jobs/create', '/jobs/create');
Route::get('/Jobs/job/{id}', fn (string $id) => redirect("/jobs/{$id}", 301))->whereNumber('id');
Route::get('/Jobs/job/{id}/edit', fn (string $id) => redirect("/jobs/{$id}/edit", 301))->whereNumber('id');

// Authentication routes (throttled, no auth middleware)
Route::get('/register', [RegisterUserController::class, 'show']);
Route::post('/register/store-role', [RegisterUserController::class, 'storeRole'])->middleware('throttle:auth');
Route::get('/register-2', [RegisterUserController::class, 'showRegisterDetails'])->name('register-2');
Route::post('/register-2/register', [RegisterUserController::class, 'register'])->middleware('throttle:auth');
Route::get('/login', [RegisterUserController::class, 'logIn']);
Route::post('/login', [RegisterUserController::class, 'authenticate'])->middleware('throttle:login')->name('login');
Route::post('/logout', [RegisterUserController::class, 'logout'])->name('logout');
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::get('/forgot-password',[ForgetPassword::class,'show']);
Route::post('/send-reset-link',[ForgetPassword::class,'sendResetLinkEmail'])->middleware('throttle:auth')->name('password.email');
Route::get('/reset-password/{token}', [ForgetPassword::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgetPassword::class, 'reset'])->middleware('throttle:auth')->name('password.update');


// Static Pages
Route::get('/about', function () {
    return Inertia::render('About');
});

Route::get('/contact', function () {
    return Inertia::render('Contact');
});

