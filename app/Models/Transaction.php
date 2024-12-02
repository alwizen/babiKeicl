<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'date',
        'amount',
        'image'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeIncome($query)
    {
        return $query->whereHas('category', function ($query) {
            $query->where('type', 'income');
        });
    }

    public function scopeExpanse($query)
    {
        return $query->whereHas('category', function ($query) {
            $query->where('type', 'expanse');
        });
    }
}
