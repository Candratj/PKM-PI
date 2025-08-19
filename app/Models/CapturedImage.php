<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapturedImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'image_path',
        'captured_at',
        'description'
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
