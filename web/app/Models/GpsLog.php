<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsLog extends Model
{
    protected $table = 'gps_logs';
    protected $primaryKey = 'id_gps';
    public $timestamps = false;

    protected $fillable = [
        'id_device',
        'latitude',
        'longitude',
        'accuracy_m',
        'recorded_at',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'id_device', 'id_device');
    }
}
