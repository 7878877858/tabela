<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'title',
        'description',
        'agenda',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'status',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function participants()
    {
        return $this->belongsToMany(
            User::class,
            'meeting_participants'
        )->withPivot('attendance_status');
    }
}