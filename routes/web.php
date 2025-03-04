<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ViewJobApplications;
use App\Http\Controllers\EmployerJobListingController;
use App\Http\Controllers\AppliedJobsController;
use App\Http\Controllers\GoogleAuthController;
use Inertia\Inertia;
use App\Http\Controllers\GPTController;
use App\Http\Controllers\TagController;


Route::get('/', function () {
    return Inertia::render('Landing');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/Home', [JobListingController::class, 'index'])->name('home');
    Route::get('/search', [JobListingController::class, 'search'])->name('search');
    Route::get('/Jobs', [JobListingController::class, 'jobs'])->name('Jobs');
    Route::get('/Jobs/create', [JobListingController::class, 'create']);
    Route::post('/Jobs', [JobListingController::class, 'store']);
    Route::get('/filter', [JobListingController::class, 'filterJobs']);
    Route::get('/Jobs/job/{jobListing}', [JobListingController::class, 'show']);


    // View all applications
    Route::get('/view-applications', [ViewJobApplications::class, 'show'])
        ->middleware('auth'); 
    
    // Apply for a job
    Route::post('/apply/{jobListing}', [ViewJobApplications::class, 'apply'])
        ->middleware('auth');
    
    // Update application status
    Route::put('/applications/{application}', [ViewJobApplications::class, 'update'])
        ->middleware('auth');
    

   
});

Route::middleware(['auth'])->group(function () {

    Route::get('/Jobs/job/{jobListing}/edit', [JobListingController::class, 'edit']);
    Route::patch('/Jobs/job/{jobListing}', [JobListingController::class, 'update']);
    Route::delete('/Jobs/job/{jobListing}', [JobListingController::class, 'destroy']);
    Route::get('/posted-jobs',[EmployerJobListingController::class,'show']);
    Route::get('/applied-jobs',[AppliedJobsController::class,'show']);
    Route::post('/chat', [GPTController::class, 'chat'])->name('chat');
    Route::get('/chat', [GPTController::class, 'getChat']);
    Route::post('/clear-chat', [GPTController::class, 'clearChat']);
    Route::get('/chat-history', [GPTController::class, 'getChatHistory']);
    Route::get('/tags',[TagController::class,'index']);
    Route::post('/create-tags',[TagController::class,'store']);
    Route::post('/delete-tags',[TagController::class,'destroy']);
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

// Static Pages
Route::get('/about', function () {
    return Inertia::render('About');
});

Route::get('/contact', function () {
    return Inertia::render('Contact');
});

Route::get('/profile', [ProfileController::class,'profile'])->middleware(['auth']);
