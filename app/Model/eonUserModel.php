<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class eonUserModel extends Model
{

    protected $connection = "mysql";
	protected $table = "eon_user_transactions";
    public $timestamps = false;
    protected $primaryKey = 'eon_user_id';
    protected $fillable = [
        'eon_user_first_name',
        'eon_user_middle_name',
        'eon_user_last_name',
        'eon_user_category',
        'eon_user_mother',
        'eon_user_mobile',
        'eon_user_email',
        'eon_user_gender',
        'eon_user_nic',
        'eon_user_title',
        'eon_user_street',
        'eon_user_city',
        'eon_user_province',
        'eon_user_postal',
        'eon_user_country',
        'eon_user_birth',
        'eon_user_birth_place',
        'eon_user_civ_stat',
        'eon_user_fund_source',
        'eon_user_status',
        'eon_user_nature',
        'eon_user_company',
        'eon_user_nationality',
        'eon_user_id_type',
        'eon_user_id_number'
    ];

    public function SaveUserInfo($data, $request){
        // dd($request);
        $saveUser = new eonUserModel;
        $saveUser->eon_user_id = $data->customerId;
        $saveUser->eon_user_first_name = $request->name->first;
        $saveUser->eon_user_middle_name = $request->name->middle;
        $saveUser->eon_user_last_name = $request->name->last;
        $saveUser->eon_user_category = $request->customerCategory;
        $saveUser->eon_user_mother = $request->mothersMaidenName;
        $saveUser->eon_user_mobile = $request->mobileNumber;
        $saveUser->eon_user_email = $request->email;
        $saveUser->eon_user_gender = $request->gender;
        $saveUser->eon_user_nic = $request->nic;
        $saveUser->eon_user_title = $request->title;
        $saveUser->eon_user_street = $request->presentAddress->line1;
        $saveUser->eon_user_city = $request->presentAddress->city;
        $saveUser->eon_user_province = $request->presentAddress->province;
        $saveUser->eon_user_postal = $request->presentAddress->postalCode;
        $saveUser->eon_user_country = $request->presentAddress->country;
        $saveUser->eon_user_birth = $request->birthDate;
        $saveUser->eon_user_birth_place = $request->birthPlace;
        $saveUser->eon_user_civ_stat = $request->civilStatus;
        $saveUser->eon_user_fund_source = $request->sourceOfFund;
        $saveUser->eon_user_status = $request->employment->status;
        $saveUser->eon_user_nature = $request->employment->natureOfWork;
        $saveUser->eon_user_company = $request->employment->companyName;
        $saveUser->eon_user_nationality = $request->nationality;
        $saveUser->eon_user_id_type = $request->idType;
        $saveUser->eon_user_id_number = $request->idNumber;
        if($saveUser->save()){
            return "Congratulations! You have successfully created your EON UBP Account! Your user id is ".$data->customerId.". Please take note of your reference number in case of inquiries ".$data->senderRefId;
        }else{
            return "Failed to create account. Please contact administrator.";
        }
    }
    
}
