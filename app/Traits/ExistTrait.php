<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Temporary_user_service;
use App\Bike;
use App\Shop;
use App\Member;
use App\All_token;
use App\Api_log;

use Auth;
use Validator;
/**
 * 
 */
trait ExistTrait
{
    public function test()
    {
        return 5000001;
    }

    /** valid user or not */
    public function validUserExistsTrait($id)
    {
        $memQry = DB::table('members')
                  ->select('member_id')
                  ->where('member_id', $id)
                  ->count();
        // print_r($memQry);    
        
        if ($memQry > 0) 
        {
            return true;
        } 
        else 
        {
            return false;
        }
        
    }

    /* Checking a specific bike exist in our database*/
    public function allBikeListExistsTrait($bikeListId)
    {
        $bikeList = DB::table('all_bike_lists')
                            ->select('bike_name')
                            ->where('bike_list_id', $bikeListId)
                            ->get();

        $bikeListRow = count($bikeList);

        $result = ($bikeListRow > 0) ? true : false ;

        return $result;
    }

    /* Insert Api Log */
    public function insertApiLogExistTrait($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl)
    {
      $apiLog = new Api_log();


      $apiLog->request_details = $requestDetails;
      $apiLog->response_details = $responseDetails;
      $apiLog->hitting_time = $this->currentDateTimeExistTrait();
      $apiLog->request_type = $apiReqType;
      $apiLog->client_ip = $clientIp;
      $apiLog->api_name = $apiName;


      $apiLog->save();

      return true;
    }

    /** Current Date Time in Bangladesh */
    public function currentDateTimeExistTrait()
    {
        date_default_timezone_set('Asia/Dhaka');
        $currentDateTime =  date('Y-m-d h:i:s');
        
        return $currentDateTime;
    }



}
