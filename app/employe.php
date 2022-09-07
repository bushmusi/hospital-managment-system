<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class employe extends Model
{
    protected $fillable = ['f_name', 'l_name','email','role'];
}
