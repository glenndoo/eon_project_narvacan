<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use DB;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class ApiPost 
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next){
    
      if($request->server('REQUEST_METHOD') == "POST"){
       return $next($request);
     }else{
      return response(json_encode(['msgCode'=> '404', 'msgText' => 'Invalid request method']));
    }
  }
}
