<?php

namespace AsayHome\AsayHelpers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsayPaymentsOperations extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'asay_payments_operations';

    protected $fillable = [
        'user_id',
        'created_by',
        'order_id',
        'operation',
        'operation_id',
        'type',
        'reason',
        'amount',
        'gateway',
        'reference',
        'details',
        'status',
    ];



    public function owner()
    {
        return $this->hasOne(UserModel::class, 'id', 'user_id');
    }
    public function creator()
    {
        return $this->hasOne(UserModel::class, 'id', 'created_by');
    }

    public function getDetailsAttribute($value)
    {
        return json_decode($value, true);
    }

    public $dates = [
        'created_at',
    ];

    protected $hidden = [
        'deleted_at',
        'updated_at',
    ];
}
