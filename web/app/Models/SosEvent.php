<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SosEvent extends Model
{
    protected $table = 'sos_events';
    protected $primaryKey = 'id_sos';
    public $timestamps = false;

    protected $fillable = [
        'id_device',
        'latitude',
        'longitude',
        'status',
        'telegram_message_id',
        'triggered_at',
        'resolved_at',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'id_device', 'id_device');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_sos', 'id_sos');
    }
}
