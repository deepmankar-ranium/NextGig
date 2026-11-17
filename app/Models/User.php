<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Employer;
use App\Models\Role;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role_id',
        'google_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'photo_path',
    ];

    /**
     * @var list<string>
     */
    protected $appends = ['photo_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * URL of the profile photo (served through an authorised route), or null for initials.
     */
    protected function photoUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(fn () => $this->photo_path
            ? route('users.photo', ['user' => $this->id, 'v' => substr(md5($this->photo_path), 0, 10)], false)
            : null);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employer()
    {
        return $this->hasOne(Employer::class);
    }

    public function jobseeker()
    {
        return $this->hasOne(Jobseeker::class);
    }
}
