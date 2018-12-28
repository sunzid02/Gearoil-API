<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\All_token;
use App\Api_log;
use Auth;
use DateTime;

class GearOilAuthApi
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
        $token = $request->header('token');

        $matchTokenRow = $this->matchToken($token);
        // echo "<pre>";
        // print_r($matchTokenRow);

        date_default_timezone_set('Asia/Dhaka');
        $currentDateTime =  date('Y-m-d h:i:s');

        // $this->timeValidity($matchTokenRow['ct'], $matchTokenRow['et']);
        // die();

        if ($matchTokenRow['status'] != true || $matchTokenRow['status'] == "" ) 
        {
            return redirect()->route('token.wrongToken');
        }
        elseif ( $matchTokenRow['status'] == true && $this->timeValidity( $matchTokenRow['ct'], $matchTokenRow['et']) == false ) 
        {   
            // echo $currentDateTime;
            // echo "gelo";
            $tokenStatusUpdate = DB::table('all_tokens')
                    ->where('token', $token)
                    ->update(['status' => 0]);

            return redirect()->route('token.expireToken');
        } 

        return $next($request);
    }

    public function matchToken($token)
    {
      if (!empty($token) || $token != '')
      {
        //matching token
         $matchToken = DB::table('all_tokens')
                  ->SELECT('token', 'create_time', 'end_time')
                  ->from('all_tokens')
                  ->where('token', $token)
                  ->where('status', 1)
                  ->get();

         $matchTokenRow = count($matchToken);

         if ($matchTokenRow > 0)
         {
           foreach ($matchToken as $key => $value)
           {
             $ct = $value->create_time;
             $et = $value->end_time;
           }

           $data = array('status' => true,
                          'ct' => $ct,
                          'et' => $et,
                        );
           return $data;
         }
         else
         {
           $data = array('status' => false,

                        );
           return $data;
         }
      }
      else
      {
        $data = array('status' => false,
                     );
        return $data;
      }
    }

    public function timeValidity($tokCrt, $tokExp)
    {
        date_default_timezone_set('Asia/Dhaka');
        $cdt =  date('Y-m-d h:i:s');

        // echo $currentDateTime =  date('Y-m-d h:i:s')."<br>";
        // echo $tokCrt."tokcreate<br>";
        // echo $tokExp."tokExp<br>";

        date_default_timezone_set('Asia/Dhaka');

        // $cdt = new DateTime();
        // $tokExp    = new DateTime($tokExp);


//         $mydate = "02/27/2012";
// echo $todaysdate=date("m/d/Y");

// if ($tokExp > $cdt)
// {
// echo "Equal or Later<br>";
// }
// else
// {
// echo "Not Equal and Not Later<br>";
// }




        // if ( ($cdt < $tokExp && $cdt > $tokCrt) || ($cdt < $tokExp && $cdt < $tokCrt)) 
        // {
        if ( $tokExp > $cdt) 
        {
            // echo "false na";
          return true;
        } 
        else 
        {
                        // echo "false";
// 
          return false;
        }
        
    }
}
