<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id_notif';
    public $timestamps = false;

    protected $fillable = [
        'id_sos',
        'id_user',
        'telegram_chat_id',
        'delivery_status',
        'sent_at',
    ];

    public function sosEvent()
    {
        return $this->belongsTo(SosEvent::class, 'id_sos', 'id_sos');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
