<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
  protected $primaryKey = 'member_id';
  public $timestamps = false;
}
