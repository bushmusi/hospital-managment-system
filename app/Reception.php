<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reception extends Model
{
   protected $fillable=['fullName','type','phone','opd_num','age','gender','reagion','subcity'];
   //protected $fillable=['fullName'];
}
