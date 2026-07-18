<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorLog extends Model
{
    protected $table = 'sensor_logs';
    protected $primaryKey = 'id_sensor';
    public $timestamps = false;

    protected $fillable = [
        'id_device',
        'distance_cm',
        'obstacle_detected',
        'recorded_at',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'id_device', 'id_device');
    }
}
