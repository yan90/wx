<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class MediaModel extends Model
{
    protected $table = "p_wx_media";
//    protected $primaryKey = " id";
    public $timestamps = false;
    protected $guarded = [];
}
