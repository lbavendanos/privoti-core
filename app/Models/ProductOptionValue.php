<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionValue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'value',
        'option_id',
    ];

    /**
     * Get the option that owns the value.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }
}
