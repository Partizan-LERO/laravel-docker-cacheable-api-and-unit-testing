<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function sellerContracts() {
        return $this->hasMany('App\Contract', 'seller_company_id');
    }

    public function clientContracts() {
        return $this->hasMany('App\Contract', 'client_company_id');
    }
}
