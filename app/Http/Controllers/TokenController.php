<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\All_token;
use App\Api_log;
use Illuminate\Support\Facades\DB;


class TokenController extends Controller
{
    public function insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl)
    {
      // $params = array(
      //                'userId' => $userId,
      //                'authenticatedId' => $authenticatedId,
      //               );
      //
      // $requestDetails = url()->current()."?".http_build_query($params);
      // $responseDetails = response()->json($data);
      // $clientIp =  $request->ip();
      // $currentUrl = $request->url();
      date_default_timezone_set("Asia/Dhaka");
      $currentDateTime = date("Y-m-d h:i:s");

      $apiLog = new Api_log();


      $apiLog->request_details = $requestDetails;
      $apiLog->response_details = $responseDetails;
      $apiLog->hitting_time = $currentDateTime;
      $apiLog->request_type = $apiReqType;
      $apiLog->client_ip = $clientIp;
      $apiLog->api_name = $apiName;


      $apiLog->save();

      return true;
    }

    public function getTokenNow(Request $request)
    {
      $reqUserName = $request->header('username');
      $reqPass = $request->header('password');

      $username = "sunzid";
      $password = "$2y$10lxT7ZXHMyYKDpFp9TBG2ge5g1YnWrglseywFA0jSpxPTKpVATAzni";

      if ($reqUserName != '' && $reqPass!='')
      {
        if ($reqUserName == $username && $password == $reqPass)
        {
          //Generate a random string.
          $token = openssl_random_pseudo_bytes(16);

          //Convert the binary data into hexadecimal representation.
          $token = bin2hex($token);
          $token = md5($token);
          $ip = $request->ip();

          date_default_timezone_set('Asia/Dhaka');
          $startTime =  date('y-m-d h:i:s');
          // date_default_timezone_set('Asia/Dhaka');

          $endTime = date("y-m-d h:i:s", strtotime('+1 Day'));

          //inserting to Database  table
          $at = new All_token();

          $at->token = $token;
          $at->create_time = $startTime;
          $at->end_time = $endTime;
          $at->client_ip = $ip;
          $at->status = 1;

          $insertionDone = $at->save();

          if ($insertionDone == 1)
          {
            $lastInsertedId = $at->id;

            $tokenDataObj = DB::table('all_tokens')->SELECT('token')->where('id', $lastInsertedId)->first();
            $tokenUser = $tokenDataObj->token;

            $data['status'] = 200;
            $data['token'] = $tokenUser;
          }
          else
          {
            $data['status'] = 500;
            $data['message'] = "server related problem, try again later";
          }
        }
        else
        {
          $data['status'] = 400;
          $data['message'] = "invalid user";
        }
      }
      else
      {
        $data['status'] = 400;
        $data['password'] = "insufficient input";
      }

      //................insert details to Api_log starts......................................
        $params = array(
                       'username' => $reqUserName,
                       'password' => $reqPass,
                      );

        $requestDetails = url()->current()."?".http_build_query($params);
        $responseDetails = response()->json($data);
        $apiName = "getTokenNow";
        $apiReqType = "GET";
        $clientIp =  $request->ip();
        $currentUrl = $request->url();

        $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

      //................insert details to Api_log ends......................................


      $response = json_encode($data, JSON_PRETTY_PRINT);
      return $response;
    }


    public function wrongToken(Request $request)
    {
      $data['status'] = 400;
      $data['message'] = "authentication failed";     
      
      $response = json_encode($data, JSON_PRETTY_PRINT);
      return $response;
    }

    public function expireToken(Request $request)
    {


      $data['status'] = 400;
      $data['message'] = "Token Expired, sorry"; 
      
      $response = json_encode($data, JSON_PRETTY_PRINT);
      return $response;
     }
}
