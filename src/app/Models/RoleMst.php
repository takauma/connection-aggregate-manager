<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class RoleMst extends Authenticatable
{
    // テーブル名.
    protected $table = "role_mst";

    const CREATED_AT = null;
    const UPDATED_AT = null;
}
