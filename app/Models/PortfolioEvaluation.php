<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'evaluated_by',
        'score',
        'comment',
        'evaluated_at'
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'evaluated_at' => 'datetime'
    ];

    /**
     * Evaluation belongs to file
     */
    public function file()
    {
        return $this->belongsTo(PortfolioFile::class, 'file_id');
    }

    /**
     * Evaluation belongs to evaluator (user)
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
