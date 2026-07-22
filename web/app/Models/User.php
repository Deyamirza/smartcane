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
    
    // Enable Laravel's default timestamps but only for created_at
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
    ];

    protected $casts = [
        'created_at' => 'datetime',
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
