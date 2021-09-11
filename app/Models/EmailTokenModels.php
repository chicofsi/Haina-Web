<?php

/*
author  : adisurizal
email   : adisurizal.cyber@outlook.com
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTokenModels extends Model {

  public $timestamps = false;
  protected $table = 'email_tokens';
  protected $guarded = [];

}
