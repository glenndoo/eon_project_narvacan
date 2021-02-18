<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class eonKeysModel extends Model
{
    protected $connection = "mysql";
	protected $table = "eon_keys";
    public $timestamps = false;
    protected $primaryKey = 'eon_secret_id';
    protected $fillable = [
        'eon_secret_key',
        'eon_secret_host'
    ];
}
