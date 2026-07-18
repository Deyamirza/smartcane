<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    
    // Disable Laravel's default timestamps since we only have created_at
    public $timestamps = false;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Override the default password field name for Laravel authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
