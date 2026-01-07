<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'faculty_id',
        'head_name',
        'phone',
        'email',
        'room_number',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Department belongs to faculty
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Department has many users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope - only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
