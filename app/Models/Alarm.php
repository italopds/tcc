<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alarm extends Model
{
    use HasFactory;

    protected $fillable = [
        'baby_id',
        'time',
        'day_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'time' => 'datetime'
    ];

    protected $appends = [
        'formatted_time'
    ];

    /**
     * Get the baby that owns the alarm.
     */
    public function baby(): BelongsTo
    {
        return $this->belongsTo(Baby::class);
    }

    public function getFormattedTimeAttribute()
    {
        return $this->time->format('H:i');
    }
} 