<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $guarded = ['id'];

    public function sellerCompany() {
        return $this->belongsTo('App\Company', 'seller_company_id');
    }

    public function clientCompany() {
        return $this->belongsTo('App\Company', 'client_company_id');
    }

    public function purchases() {
        return $this->hasMany('App\Purchase');
    }
}
