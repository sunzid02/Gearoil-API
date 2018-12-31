<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Member;
use App\Api_log;

use Validator;

class MemberController extends Controller
{
    public function insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl)
    {
      // $params = array(
      //                'userId' => $userId,
      //                'authenticatedId' => $authenticatedId,
      //              );
      //
      // $requestDetails = url()->current()."?".http_build_query($params);
      // $responseDetails = response()->json($data);
      // $clientIp =  $request->ip();
      // $currentUrl = $request->url();

      $apiLog = new Api_log();


      $apiLog->request_details = $requestDetails;
      $apiLog->response_details = $responseDetails;
      $apiLog->hitting_time = $this->currentDateTime();
      $apiLog->request_type = $apiReqType;
      $apiLog->client_ip = $clientIp;
      $apiLog->api_name = $apiName;


      $apiLog->save();

      return true;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['status'] = '404';
        $data['message'] = "Method not allowed";

        $response =  json_encode($data, JSON_PRETTY_PRINT);
        return $response;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $username = $request->username;
        $email = $request->email;
        $fbId = $request->fbId;
     
        $validator = Validator::make($request->all(), [
          'username' => 'required | max:30',
          'email' => 'max:30 | required | email | unique:members,email',
          'fbId' => 'max:255 | required | unique:members,firebase_id',
        ]);

        if($validator->fails())
        {
          $data['status'] = '400';
          $data['message'] = "invalid input found, pass the data with desire format";
          $data['data'] =  $validator->errors();
        }
        else 
        {      
            $userrole =  3; // 3 is for normal users           

            $member = new Member();

            $member->username = $username;
            $member->userrole = $userrole;
            $member->email = $email;
            $member->created_at = $this->currentDateTime();
            $member->updated_at = $this->currentDateTime();
            $member->firebase_id = $fbId;


            $member->save();



            if ($member->save() == 1) 
            {
                $data['status'] = '200';
                $data['message'] = 'members registered successfully';            
            } 
            else 
            {
                $data['status'] = '500';
                $data['message'] = 'Server error !! registration failed';              
            }            
        }

       //................insert details to Api_log starts......................................
            $params = array(
                        'username' => $username,
                        'email' => $email,
                        'fbId' => $fbId,
                        );

            $requestDetails = url()->current()."?".http_build_query($params);
            $responseDetails = response()->json($data);
            $apiName = "memberStore";
            $apiReqType = "POST";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

      //................insert details to Api_log ends......................................

        
        $response =  json_encode($data, JSON_PRETTY_PRINT);
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $memberId = $id; 
        // `username` `email 
        $userExists = $this->validUser($memberId);
        $username = $request->username;
        $email = $request->email;
        
        if ($userExists == true) 
        {   


            $validator = Validator::make($request->all(), [
                'username' => 'required | max:30',
                'email' => 'max:30 | required | email ',
            ]);

            if($validator->fails())
            {
                $data['status'] = '400';
                $data['message'] = "invalid input found, pass the data with desire format";
                $data['data'] =  $validator->errors();
            }
            else 
            {
                $update = DB::table('members')->where('firebase_id', $memberId)
                ->update(
                            [
                                'username' => $username,
                                'email' => $email,
                                'updated_at' => $this->currentDateTime(),
                            ]
                        );

                if ($update == 1) 
                {
                    $data['status'] = '200';
                    $data['message'] = "information updated";
                } 
                else 
                {
                    $data['status'] = '500';
                    $data['message'] = "server problem, update failed";                
                }                        

            }

        } 
        else 
        {
            $data['status'] = '400';
            $data['message'] = "user not found";
        }
        
        
       //................insert details to Api_log starts......................................
            $params = array(
                        'id' => $id,
                        'email' => $email,
                        'username' => $username,
                        );

            $requestDetails = url()->current()."?".http_build_query($params);
            $responseDetails = response()->json($data);
            $apiName = "memberUpdate";
            $apiReqType = "PUT";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

      //................insert details to Api_log ends......................................

        $response =  json_encode($data, JSON_PRETTY_PRINT);
        return $response;
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /** valid user or not */
    public function validUser($id)
    {
        $memQry = DB::table('members')
                  ->select('member_id')
                  ->where('firebase_id', $id)
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

    public function currentDateTime()
    {
        date_default_timezone_set('Asia/Dhaka');
        $currentDateTime =  date('Y-m-d h:i:s');
        
        return $currentDateTime;
    }
}
