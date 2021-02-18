<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class eonUserModel extends Model
{
    protected $connection = "mysql";
	protected $table = "eon_user_details";
    public $timestamps = false;
    protected $primaryKey = 'eon_user_id';
    protected $fillable = [
        'eon_user_description',
        'eon_ref_id'
    ];

    public function SaveVirtualCard($data, $request){
        // dd($data);
        $saveVirtual = new eonUserModel;
        $saveVirtual->eon_user_id = $data->customerId;
        $saveVirtual->eon_description = $data->description.'|Create Card|'.$data->accountNumber.'|'.$data->token.'|'.$data->apiCardNumber;
        $saveVirtual->eon_ref_id = $data->uuid;
        if($saveVirtual->save()){
            return "Successfully saved virtual card!";
        }else{
            return "Failed to create a new virtual card";
        }
    }
}
