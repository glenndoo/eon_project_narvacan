<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class eonTokenModel extends Model
{
    protected $connection = "mysql";
	protected $table = "eon_token";
    public $timestamps = false;
    protected $primaryKey = 'eon_token_id';
    protected $fillable = [
        'eon_token_name',
        'eon_token_token',
        'eon_token_create',
        'eon_token_scope'
    ];

    
}
