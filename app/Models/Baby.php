<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;

class Baby extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'name',
        'birth_date'
    ];

    protected $casts = [
        'birth_date' => 'date'
    ];

    /**
     * Get the user that owns the baby.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the feedings for the baby.
     */
    public function feedings(): HasMany
    {
        return $this->hasMany(Feeding::class);
    }

    /**
     * Get the alarms for the baby.
     */
    public function alarms(): HasMany
    {
        return $this->hasMany(Alarm::class);
    }

    /**
     * Get the notifications for the baby.
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Get the baby's age in months.
     */
    public function getAgeInMonthsAttribute(): int
    {
        return $this->birth_date->diffInMonths(Carbon::now());
    }
} 