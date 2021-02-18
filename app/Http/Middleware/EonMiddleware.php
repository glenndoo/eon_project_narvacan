<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Illuminate\Support\Arr;
use App\Model\eonKeysModel;

class EonMiddleware
{
  public $eonKeysModel;

  public function __construct(eonKeysModel $eonKeysModel){
    $this->eonKeysModel = $eonKeysModel;
  }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Arr::has($request->header(),'narv-secret')){
            if($request->header('narv-secret') != null){
             $getkey =$this->eonKeysModel->first('eon_secret_key');
             if($getkey->eon_secret_key == $request->header('narv-secret')){
               
             }else{
               return response(json_encode(['msgCode'=> '404', 'msgText' => 'Invalid secret key']));
             }
           }else{
            return response(json_encode(['msgCode'=> '404', 'msgText' => 'null parameter value header']));
          }
        }else{
         return response(json_encode(['msgCode'=> '404', 'msgText' => 'Not Authenticated']));
       }
        return $next($request);
    }
}
