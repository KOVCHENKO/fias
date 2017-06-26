<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Add extends Model
{
    protected $table = 'addrs';
    protected $primaryKey = 'AOID';
    public $timestamps = false;
}
