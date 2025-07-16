<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserRole extends Authenticatable
{
    // テーブル名.
    protected $table = "user_role";

    const CREATED_AT = null;
    const UPDATED_AT = null;
}