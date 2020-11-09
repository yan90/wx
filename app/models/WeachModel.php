<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class WeachModel extends Model
{
    protected $table = "p_wechat";
//    protected $primaryKey = "c_id";
    public $timestamps = false;
    protected $guarded = [];
}
