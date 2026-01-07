<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'year',
        'description',
        'status',
        'uploaded_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'integer',
        'year' => 'integer'
    ];

    /**
     * File belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * File belongs to category
     */
    public function category()
    {
        return $this->belongsTo(PortfolioCategory::class, 'category_id');
    }

    /**
     * File has one evaluation
     */
    public function evaluation()
    {
        return $this->hasOne(PortfolioEvaluation::class, 'file_id');
    }

    /**
     * Scope - by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope - pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope - evaluated
     */
    public function scopeEvaluated($query)
    {
        return $query->where('status', 'evaluated');
    }
}
