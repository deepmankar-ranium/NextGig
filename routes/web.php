<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\RegisterUserController;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Landing');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/Home', [JobListingController::class, 'index'])->name('home');
    Route::get('/search', [JobListingController::class, 'search'])->name('search');
    Route::get('/Jobs', [JobListingController::class, 'jobs']);
    Route::get('/Jobs/create', [JobListingController::class, 'create']);
    Route::post('/Jobs', [JobListingController::class, 'store']);
    Route::get('/Jobs/job/{jobListing}', [JobListingController::class, 'show']);
   
});

Route::middleware(['auth'])->group(function () {

    Route::get('/Jobs/job/{jobListing}/edit', [JobListingController::class, 'edit']);
    Route::patch('/Jobs/job/{jobListing}', [JobListingController::class, 'update']);
    Route::delete('/Jobs/job/{jobListing}', [JobListingController::class, 'destroy']);
});

// Authentication routes (no middleware)
Route::get('/register', [RegisterUserController::class, 'show']);
Route::post('/register', [RegisterUserController::class, 'register']);
Route::get('/login', [RegisterUserController::class, 'logIn']);
Route::post('/login', [RegisterUserController::class, 'authenticate'])->name('login');
Route::post('/logout', [RegisterUserController::class, 'logout'])->name('logout');

// Static Pages
Route::get('/about', function () {
    return Inertia::render('About');
});

Route::get('/contact', function () {
    return Inertia::render('Contact');
});

Route::get('/profile', function () {
    return Inertia::render('Profile');
})->middleware(['auth']);
