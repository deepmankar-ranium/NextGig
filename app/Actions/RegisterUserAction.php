<?php 
namespace App\Actions;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Employer;
use App\Models\JobSeeker;
use Laravel\Socialite\Facades\Socialite;
 
class RegisterUserAction{
    public function registerUser($request, $role_id, $validatedData){
                 // Hash password
                 $validatedData['password'] = Hash::make($validatedData['password']);
                 // Create User
                 $user = User::create([
                     'full_name' => $validatedData['full_name'],
                     'email' => $validatedData['email'],
                     'password' => $validatedData['password'],
                     'role_id' => $role_id, // Assign role_id from session
                 ]);
             
                 // Insert into `employers` if role_id is 2
                 if ($role_id == 2) {
                     Employer::create([
                         'user_id' => $user->id,
                         'name' => $request->input('name', $user->full_name),
                         'email' => $user->email,
                         'address' => $request->input('address', 'Not Provided'),
                         'phone' => $request->input('phone', null),
                         'description' => $request->input('description', null),
                     ]);
                 }
                 // Insert into `job_seekers` if role_id is 3
                 elseif ($role_id == 3) {
                     JobSeeker::create([
                         'user_id' => $user->id,
                         'name' => $request->input('name', $user->full_name),
                         'email' => $user->email,
                         'resume_link' => $request->input('resume_link', null),
                     ]);
                 }
             
                 // Log in user
                 Auth::login($user);

    }

    
    public function googleRegister(){
        try {
            $googleUser = Socialite::driver('google')->user();

            $role_id = session('user_role'); // Retrieve role_id from session
    
            // Check if user already exists in our database
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Store new user in database with a default role (change if needed)
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(str()->random(16)), // Random password since Google handles authentication
                    'role_id' => $role_id, // Assign role_id from session
                    'google_id' => $googleUser->getId(), // Store Google ID for reference
                ]);

                // Insert into `employers` if role_id is 2
                if ($role_id == 2) {
                    Employer::create([
                        'user_id' => $user->id,
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'address' => 'Not Provided',
                        'phone' => null,
                        'description' => null,
                    ]);
                }
                // Insert into `job_seekers` if role_id is 3
                elseif ($role_id == 3) {
                    JobSeeker::create([
                        'user_id' => $user->id,
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'resume_link' => null,
                    ]);
                }
            }

            // Log in the user
            Auth::login($user);

            return redirect('/Home'); 
        } catch (\Exception $e) {
            return redirect('/register')->with('error', 'Something went wrong. Please try again.');
        }

    }
}