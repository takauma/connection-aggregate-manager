<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserMst extends Authenticatable
{
    // テーブル名.
    protected $table = "user_mst";

    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];
}
