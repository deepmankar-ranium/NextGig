<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\EmployerJobListingController;
use App\Http\Controllers\AppliedJobsController;
use App\Http\Controllers\ForgetPassword;
use App\Http\Controllers\GoogleAuthController;
use Inertia\Inertia;
use App\Http\Controllers\GPTController;
use App\Http\Controllers\sendResetPasswordLink;
use App\Http\Controllers\TagController;
use App\Http\Controllers\MessagesController;


Route::get('/', function () {
    return Inertia::render('Landing');
});

Route::middleware(['auth'])->group(function () {
    // Job Listing routes
    Route::get('/Home', [JobListingController::class, 'index'])->name('home');
    Route::get('/Jobs', [JobListingController::class, 'jobs'])->name('Jobs');
    Route::get('/Jobs/create', [JobListingController::class, 'create']);
    Route::post('/Jobs/create', [JobListingController::class, 'store'])->name('jobs.store');
    Route::get('/Jobs/job/{jobListing}', [JobListingController::class, 'show']);
    Route::get('/Jobs/job/{jobListing}/edit', [JobListingController::class, 'edit']);
    Route::patch('/Jobs/job/{jobListing}', [JobListingController::class, 'update']);
    Route::delete('/Jobs/job/{jobListing}', [JobListingController::class, 'destroy']);
    Route::get('/search', [JobListingController::class, 'search'])->name('search');
    Route::get('/filter', [JobListingController::class, 'filterJobs']);

    // Application routes
    Route::get('/view-applications', [JobApplicationController::class, 'show']);
    Route::post('/apply/{jobListing}', [JobApplicationController::class, 'apply']);
    Route::put('/applications/{application}', [JobApplicationController::class, 'update']);

    // User-specific job routes
    Route::get('/posted-jobs', [EmployerJobListingController::class, 'show']);
    Route::get('/applied-jobs', [AppliedJobsController::class, 'show']);

    // Chat routes
    Route::post('/chat', [GPTController::class, 'chat'])->name('chat');
    Route::get('/chat', [GPTController::class, 'getChat']);
    Route::post('/clear-chat', [GPTController::class, 'clearChat']);
    Route::get('/chat-history', [GPTController::class, 'getChatHistory']);
    Route::post('/create-intro-message', [GPTController::class, 'createIntroMessage'])->name('chat.intro');


    // Inbox chat routes
    Route::get('/messages/{user?}', [MessagesController::class, 'show'])->name('messages.show');
    Route::post('/messages', [MessagesController::class, 'store'])->name('messages.store');
    Route::post('/messages/delete/{sender_id}/{receiver_id}', [MessagesController::class, 'deleteMessages'])->name('messages.delete');

    // Tag routes
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/create-tags', [TagController::class, 'store']);
    Route::post('/delete-tags', [TagController::class, 'destroy']);

    // Profile routes
    Route::get('/profile', [ProfileController::class,'profile']);
});

// Authentication routes (no middleware)]
Route::get('/register', [RegisterUserController::class, 'show']);
Route::post('/register/store-role', [RegisterUserController::class, 'storeRole']);
Route::get('/register-2', [RegisterUserController::class, 'showRegister2'])->name('register-2');
Route::post('/register-2/register', [RegisterUserController::class, 'register']);
Route::get('/login', [RegisterUserController::class, 'logIn']);
Route::post('/login', [RegisterUserController::class, 'authenticate'])->name('login');
Route::post('/logout', [RegisterUserController::class, 'logout'])->name('logout');
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::get('/forgot-password',[ForgetPassword::class,'show']);
Route::post('/send-reset-link',[ForgetPassword::class,'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgetPassword::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgetPassword::class, 'reset'])->name('password.update');


// Static Pages
Route::get('/about', function () {
    return Inertia::render('About');
});

Route::get('/contact', function () {
    return Inertia::render('Contact');
});

