<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'full_name',
        'faculty_id',
        'department_id',
        'passport_series',
        'birth_date',
        'scientific_degree',
        'phone',
        'email',
        'avatar',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'password' => 'hashed',
    ];

    // Passport seriyasini avtomatik uppercase
    public function setPassportSeriesAttribute($value)
    {
        $this->attributes['passport_series'] = strtoupper($value);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Check if user has role
     */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * User has many portfolio files
     */
    public function portfolioFiles()
    {
        return $this->hasMany(PortfolioFile::class);
    }

    /**
     * User has many evaluations (as evaluator)
     */
    public function evaluations()
    {
        return $this->hasMany(PortfolioEvaluation::class, 'evaluated_by');
    }

    /**
     * User has many test permissions
     */
    public function testPermissions()
    {
        return $this->hasMany(UserTestPermission::class);
    }

    /**
     * User has many test results
     */
    public function testResults()
    {
        return $this->hasMany(TestResult::class);
    }
}
