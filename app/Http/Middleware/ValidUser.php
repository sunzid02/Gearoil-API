<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


use Closure;

class ValidUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $userId = $request->userId;

        if ($userId != '' || !empty($userId || $userId != null)) 
        {
            $memQry = DB::table('members')
                        ->select('member_id')
                        ->where('member_id', $userId)
                        ->count();
            //  echo $memQry;
            //  die();           
        // print_r($memQry);    
        
            if ($memQry == 0) 
            {
                return redirect()->route('invalid.invalidUser');
            } 
        } 
        else 
        {
            return redirect()->route('invalid.insuffInput');
        }       

        return $next($request);
    }
}
