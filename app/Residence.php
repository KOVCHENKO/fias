<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    protected $table = 'hous';
    protected $primaryKey = 'HOUSEID';
    public $timestamps = false;
}
