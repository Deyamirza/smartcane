<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';
    protected $primaryKey = 'id_device';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'device_name',
        'mac_address',
        'status',
        'registered_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function sensorLogs()
    {
        return $this->hasMany(SensorLog::class, 'id_device', 'id_device');
    }

    public function gpsLogs()
    {
        return $this->hasMany(GpsLog::class, 'id_device', 'id_device');
    }

    public function sosEvents()
    {
        return $this->hasMany(SosEvent::class, 'id_device', 'id_device');
    }
}
