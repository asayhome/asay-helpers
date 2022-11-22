<?php

namespace AsayHome\AsayHelpers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsayUsersLogs extends Model
{
    use HasFactory, SoftDeletes;

    public $fillable = [
        'user_id',
        'operation',
        'device_type',
        'os_type',
        'os_version',
        'app_version',
        'location',
        'ip_address',
        'notes',
    ];
}
