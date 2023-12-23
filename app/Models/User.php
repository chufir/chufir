<?php
namespace App\Models;
use Modules\User\Models\Wallet\Transaction;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends \App\User implements Wallet
{
    use HasWallet;
    // public function transactions(){
    //     return $this->hasMany(Transaction::class,'uuid');
    // }
}