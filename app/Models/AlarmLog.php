<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'activated_at',
        'deactivated_at',
        'status',
        'message'
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
