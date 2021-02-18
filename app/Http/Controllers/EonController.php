<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;
use Hash;
use Validator;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Model\eonCredentialsModel;
use App\Model\eonTokenModel;
use App\Model\eonUserModel;

class EonController extends Controller
{
    public $eonCredentialsModel;
    public $eonTokenModel;
    public $eonUserModel;

    public function __construct(eonCredentialsModel $eonCredentialsModel, eonTokenModel $eonTokenModel, eonUserModel $eonUserModel){
        $this->eonCredentialsModel = $eonCredentialsModel;
        $this->eonTokenModel = $eonTokenModel;
        $this->eonUserModel = $eonUserModel;
    }

    public function CreateEonToken(){
    $eonConn = $this->eonCredentialsModel->where('eon_api_name','=','EON')->first();
    
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $eonConn->eon_api_base_url.'partners/v1/oauth2/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'grant_type=password&client_id='.$eonConn->eon_api_client_id.'&username='.$eonConn->eon_api_username.'&password='.$eonConn->eon_api_password.'&scope=eon_transfers%20eon_wallet',
        CURLOPT_HTTPHEADER => array(
            'accept: text/html',
            'content-Type: application/x-www-form-urlencoded',
            'x-ibm-client-id: '.$eonConn->eon_api_client_id,
            'x-ibm-client-secret: '.$eonConn->eon_api_secret,
            'x-partner-id: '.$eonConn->eon_api_partner_id,
        ),
        ));
        $now = Carbon::now();
        // dd($now);
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        if($response->access_token){
            $EndTime = date('Y-m-d H:i:s',strtotime($now.'+ '.$response->expires_in.' second'));
            $insertToken = $this->eonTokenModel->where('eon_token_name','=','Eon UBP')->update(['eon_token_token' => $response->access_token, 'eon_token_expiry' => $EndTime, 'eon_token_creation' => $now->toDateTimeString()]);
            return "Token created successfully!";
        }else{
            return "Cannot create token";
        }

  
    }
    //Create customer profile
    public function createCustomerProfile(Request $request){
        $UserInput = json_decode($request->getContent());

        $eonConn = $this->eonCredentialsModel->where('eon_api_name','=','EON')->first();
        $token = $this->EstablishTokens();
        
        $date = Carbon::now();
        // dd($date);
        $digits = 6;
        // dd($Details);

        $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => $eonConn->eon_api_base_url.'partners/eon/wallet/v1/customers/profile',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "senderRefId": "SQ'.rand(pow(10, $digits-1), pow(10, $digits)-1).'",
                "tranRequestDate": "'.$date.'",
                "name": {
                    "first": "'.$UserInput->name->first.'",
                    "middle": "'.$UserInput->name->middle.'",
                    "last": "'.$UserInput->name->last.'"
                },
                "customerCategory": "NORM",
                "mothersMaidenName": "'.$UserInput->mothersMaidenName.'",
                "mobileNumber": "'.$UserInput->mobileNumber.'",
                "email": "'.$UserInput->email.'",
                "gender": "'.$UserInput->gender.'",
                "title": "'.$UserInput->title.'",
                "presentAddress": {
                    "line1": "'.$UserInput->presentAddress->line1.'",
                    "line2": "",
                    "line3": "",
                    "city": "'.$UserInput->presentAddress->city.'",
                    "province": "'.$UserInput->presentAddress->province.'",
                    "postalCode": '.$UserInput->presentAddress->postalCode.',
                    "country": "'.$UserInput->presentAddress->country.'"
                },
                "birthDate": "'.$UserInput->birthDate.'",
                "birthPlace": "'.$UserInput->birthPlace.'",
                "civilStatus": "'.$UserInput->civilStatus.'",
                "sourceOfFund": "'.$UserInput->sourceOfFund.'",
                "employment": {
                    "status": "'.$UserInput->employment->status.'",
                    "natureOfWork": "'.$UserInput->employment->natureOfWork.'",
                    "companyName": "'.$UserInput->employment->companyName.'"
                },
                "nationality": "'.$UserInput->nationality.'",
                "idType": "'.$UserInput->idType.'",
                "idNumber": "'.$UserInput->idNumber.'"
            }',
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'authorization: Bearer '.$token->eon_token_token,
                'content-type: application/json',
                'x-ibm-client-id: '.$eonConn->eon_api_client_id,
                'x-ibm-client-secret: '.$eonConn->eon_api_secret,
                'x-partner-id: '.$eonConn->eon_api_partner_id
            ),
            ));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);
            // return json_encode($response);
            if(isset($response->errors[0]->code) == "TF"){
                return "Message from server: ".$response->errors[0]->details->message;
            }else if($response->code == "TS"){
                $insertData = $this->eonUserModel->SaveUserInfo($response, $UserInput);
                return $insertData;
            }else{
                return json_encode($response);
            }
    }
    
    public function CreateVirtualCard(Request $request){
        $eonConn = $this->eonCredentialsModel->where('eon_api_name','=','EON')->first();
        $token = $this->EstablishTokens();
        // dd($token);
        if(!empty($token)){
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => $eonConn->eon_api_base_url.'partners/eon/wallet/v1/accounts/virtual',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "customerId": "2892178",
                "productType": "PRD9183"
            }',
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'authorization: Bearer '.$token->eon_token_token,
                'x-ibm-client-id: '.$eonConn->eon_api_client_id,
                'x-ibm-client-secret: '.$eonConn->eon_api_secret,
                'x-partner-id: '.$eonConn->eon_api_partner_id,
                'Content-Type: application/json'
            ),
            ));

            $response = json_decode(curl_exec($curl));

            curl_close($curl);
            if(isset($response->code) == 'TS'){
                $insertVirtualCard = $this->eonCardModel->SaveVirtualCard($response,$request);
                return $insertVirtualCard;
            }else{
                return json_encode($response);
            }
        }else{
            return "Token expired";
        }

    }

    public function EonCheckBal(){
        $eonConn = $this->AccApiCredentailsModel->where('ApiAccName','=','EON')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $eonConn->BaseUrl.'partners/eon/wallet/v1/balance/inquiry',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'card-token: 31c42dfb9094c1a931b05b8cc3abf51f3289f562aa6879ca0a258064fa316c9367a94601db2051b72de2533a2b69715a',
            'content-type: application/json',
            'x-ibm-client-id: '.$eonConn->eon_api_client_id,
            'x-ibm-client-secret: '.$eonConn->eon_api_secret,
            'x-partner-id: '.$eonConn->eon_api_partner_id
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    public function ActivateEonCard(){
        $eonConn = $this->AccApiCredentailsModel->where('ApiAccname','=','EON')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $eonConn->BaseUrl.'partners/eon/wallet/v2/cards/activation',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "senderRefId":"REPLACE SENDERREFID",
            "tranRequestDate":"REPLACE TIMESTAMP"
        }',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'card-token: 07b6f0e099b5d4447e1317a7b61237d4201b43ebdfe7a98e6e23d16fdc5990d44da5c04206000535ccc685468dbf266d',
            'authorization: Bearer '.$token->ApiAccToken,
            'content-type: application/json',
            'x-ibm-client-id: '.$eonConn->ClientId,
                'x-ibm-client-secret: '.$eonConn->Secret,
                'x-partner-id: '.$eonConn->ApiAccNo,
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

    }



    

    public function VerifyForCreation(Request $request){
        $Details = $this->PermanentAddressModel->join('personalinformation','personalinformation.EntityId','=','permanentaddress.entityId')
        ->join('otherinformation','otherinformation.entityId','=','permanentaddress.entityId')
        ->join('govermentid','govermentid.entityId','=','permanentaddress.entityId')
        ->select('firstName','middleName','lastName','mothersMaidenName','mobilePhone','emailAddress','gender','numberStreet','cityTown','provState','postalCode','country','placeOfBirth','employer')
        ->where('personalInformation.EntityId','=',$request->entityID)->first();
        // dd($Details);
        if($Details){
            // $insertEonDetails = $this->EonInfoModel->saveDetails($Details);
            $details = $this->createCustomerProfile($request, $Details);
            return $details;
        }else{
            return "Please check your profile details if you have completed all the fields and if your Government IDs are verified. Please contact Squidpay support right away.";
        }
    }

    public function EstablishTokens(){
        $current = Carbon::now();
        
        $token = $this->eonTokenModel->where('eon_token_name','=','EON UBP')->first();
        if($current->toDateTimeString() < $token->eon_token_expiry){

        }else{
            return "Token expired";
        }
        return $token;
    }
    // public function GetQR(Request $request)
    // {
    //     dd('sampoke');
    // }

    public function GenerateQR(Request $request)
    {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://api.qrserver.com/v1/create-qr-code/?data=MTIzNDY1Nzk4&size=%5Bpixels%5Dx%5Bpixels%5D',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
          'data: '
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      return response($response);

    }

    public function AdditionalDetails()
    {
        $arr = [
            $gender = [
                'Male'  =>  'Male',
                'Female'    =>  'Female',
            ],
            $civilStatus = [
                'Married' =>  'M',
                'Single' =>  'S',
                'Divorced' =>  'D',
                'Widowed' =>  'W',
            ],
            $sourceOfFund = [
                'Allowance'   =>  '001',
                'Business Profit'   =>  '002',
                'Commissions'   =>  '003',
                'Inheritance'   =>  '004',
                'Loan'   =>  '005',
                'Salary'   =>  '006',
                'Pension'   =>  '007',
                'Remittance'   =>  '008',
            ],
            $employementStatus = [
                'Employed'   =>  'EMP',
                'Consultant'   =>  'CON',
                'Self Employed with own business'   =>  'SEB',
                'Self Employed as online freelance'   =>  'SEF',
                'Unemployed, Housewife'   =>  'UEH',
                'Unemployed, Retired'   =>  'UER',
                'Unemployed, Student'   =>  'UES',
            ],
            $natureOfWork = [
                'Agriculture' => 'Agri',
                'Banking Institutions' => 'BNK',
                'Computer and Information Technology' => 'IT',
                'Construction/Contractors' => 'CONS',
                'Consultancy/Agencies' => 'AGN',
                'Education' => 'EDUC',
                'Engineering' => 'ENG',
                'Entertainment' => 'ENT',
                'Financial Services' => 'FIN',
                'Government' => 'GOV',
                'Hotel and Restaurant Services' => 'HRS',
                'Household Employees' => 'HSE',
                'Manufacturing and Inventory' => 'INV',
                'Medical and Health Services' => 'MED',
                'Community, Social, and Personal Service' => 'SOC',
                'Others' => 'OTH',
                'Public Relations' => 'PR',
                'Real Estate' => 'EST',
                'Rental Services' => 'RENT',
                'Sales/Marketing/Advertising' => 'MKTG',
                'Science and Technology Services' => 'SCI',
                'Student' => 'STD',
                'Transportation and Communication Services' => 'TCS'
            ],

            $idType = [
                'SSS'   =>  'SSS',
                'GSIS'  =>  'GSIS',
                'TIN'   =>  'TIN',
                'DRL'   =>  'DRL',
                'AICR'  =>  'AICR',
                'NBI'   =>  'NBI',
                'PAS'   =>  'PAS',
                'PHID'  =>  'PHID',
                'SCH'   =>  'SCH',
                'UMID'  =>  'UMID',
                'VID'   =>  'VID',
                'PRC'   =>  'PRC',
            ],
        ];

        return $arr;
    }
}

