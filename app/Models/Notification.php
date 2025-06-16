<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    public function getTitleAttribute()
    {
        return $this->data['title'] ?? '';
    }

    public function getMessageAttribute()
    {
        return $this->data['message'] ?? '';
    }

    public function getIsReadAttribute()
    {
        return $this->read_at !== null;
    }
} 