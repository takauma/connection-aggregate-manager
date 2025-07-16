<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserRoleView extends Authenticatable
{
    // テーブル名.
    protected $table = "user_role_view";
}
