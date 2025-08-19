<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_name',
        'location',
        'ip_address',
        'status',
        'last_seen'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    public function motionDetections()
    {
        return $this->hasMany(MotionDetection::class, 'device_id', 'device_id');
    }

    public function alarmLogs()
    {
        return $this->hasMany(AlarmLog::class, 'device_id', 'device_id');
    }

    public function capturedImages()
    {
        return $this->hasMany(CapturedImage::class, 'device_id', 'device_id');
    }

    public function isOnline()
    {
        return $this->status === 'online' &&
            $this->last_seen &&
            $this->last_seen->diffInMinutes(now()) < 5;
    }
}
