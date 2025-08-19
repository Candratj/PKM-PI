<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotionDetection extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'detected_at',
        'alert_type',
        'message',
        'is_read'
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
