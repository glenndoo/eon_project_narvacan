<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class eonCredentialsModel extends Model
{
    protected $connection = "mysql";
	protected $table = "eon_credentials";
    public $timestamps = false;
    protected $primaryKey = 'eon_api_id';
    protected $fillable = [
        'eon_api_name',
        'eon_api_partner_id',
        'eon_api_secret',
        'eon_api_client_id',
        'eon_api_base_url'
    ];

    
}
