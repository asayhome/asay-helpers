<?php

namespace AsayHome\AsayHelpers\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserModel extends Model implements Wallet, WalletFloat
{
    use HasFactory, SoftDeletes, HasWalletFloat;

    public $table = 'users';

    public function getNameAttribute($value)
    {
        if (isset($this->first_name) && isset($this->last_name)) {
            return $this->first_name . ' ' . $this->last_name;
        } else {
            return $value;
        }
    }
}
