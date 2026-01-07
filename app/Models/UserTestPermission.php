<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTestPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_id',
        'is_allowed',
        'allowed_by',
        'allowed_at'
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
        'allowed_at' => 'datetime'
    ];

    /**
     * Permission belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Permission belongs to test
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Permission given by user
     */
    public function allowedBy()
    {
        return $this->belongsTo(User::class, 'allowed_by');
    }
}
