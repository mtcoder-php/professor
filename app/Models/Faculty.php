<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'dean_name',
        'phone',
        'email',
        'address',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Fakultetning kafedralar
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Fakultetning o'qituvchilari
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope - faqat faol fakultetlar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
