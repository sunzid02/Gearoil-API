<?php

namespace App\Http\Controllers;

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

class UserController extends Controller
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

    public function currentDateTime()
    {
        date_default_timezone_set('Asia/Dhaka');
        $currentDateTime =  date('Y-m-d h:i:s');
        
        return $currentDateTime;
    }

    public function currentMonthCost(Request $request)
    {
      //getting the userId
      $userId = $request->userId;
      $token = $request->header('token');

      //token or userId not empty
      if ($userId!='')
      {

          date_default_timezone_set('Asia/Dhaka');
          $currentDateTime =  date('Y-m-d h:i:s');

            // echo "token valid";
          $currentMonthName = date('F');
          $firstDateCurrentMonth = date('Y-m-d',strtotime('first day of this month'))." 00:00:00";
          $lastDateCurrentMonth = date('Y-m-d',strtotime('last day of this month'))." 23:59:59";

          $validUser = Member::find($userId);
          // print_r($validUser);
          $validUserRow =  count($validUser);

        if ($validUserRow > 0)
        {
          $monthlyCostAllUsers = DB::table('temporary_user_services')
                ->SELECT('b.username', 'a.member_id',DB::raw('SUM(a.service_amount) as monthly_cost'))
                ->FROM('temporary_user_services AS a')
                ->leftJoin('members AS b', 'a.member_id', '=', 'b.member_id')
                ->where('a.member_id', $userId)
                ->whereBetween('service_date_time', [$firstDateCurrentMonth, $lastDateCurrentMonth])
                ->groupBy('a.member_id')
                ->first();

          $totalServiceCost =   DB::table('members')
                                ->SELECT('user_yearly_expenditure', 'member_id', 'username')
                                ->where('member_id', $userId)
                                ->first();    



            $row = count($monthlyCostAllUsers);

            if ($row > 0)
            {
              $totalServ = $totalServiceCost->user_yearly_expenditure;
              $totC = "".$totalServ."";

              $information = array(
                      'userId' => $monthlyCostAllUsers->member_id, 
                      'username' => $monthlyCostAllUsers->username, 
                      'currentMonthName' => $currentMonthName, 
                      'totalServicingCost' => $totC,
                      'currentMonthCost' => $monthlyCostAllUsers->monthly_cost, 
                    );
              $data['status'] = 200;
              $data['currentMonth'] = $currentMonthName;
              $data['data'] = $information;
            }
            else
            {
              $totalServ = $totalServiceCost->user_yearly_expenditure;
              $totC = "".$totalServ."";
              $information = array(
                'userId' => $totalServiceCost->member_id, 
                'username' => $totalServiceCost->username, 
                'currentMonthName' => $currentMonthName, 
                'totalServicingCost' => $totC ,
                'currentMonthCost' => "0.00", 
              );
              $data['status'] = 400;
              $data['currentMonth'] = $currentMonthName;
              $data['message'] = "no data found for this month";
              $data['data'] = $information;
            }
          }
          else
          {
            $data['status'] = 404;
            $data['message'] = "user not found";
          }  
      }
      else
      {
        $data['status'] = 400;
        $data['message'] = "insufficient input";
      }




      //................insert details to Api_log starts......................................
        $params = array(
                       'userId' => $userId,
                      );
        $requestDetails = url()->current()."?".http_build_query($params);
        $responseDetails = response()->json($data);
        $apiName = "currentMonthCost";
        $apiReqType = "GET";
        $clientIp =  $request->ip();
        $currentUrl = $request->url();

        $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

      //................insert details to Api_log ends......................................





      $monthlyCostAllUsersResponse = json_encode($data, JSON_PRETTY_PRINT);
      return $monthlyCostAllUsersResponse;
    }




/*.................................User Servicing insertion starts...............................................................................*/

// http://localhost:8000/api/user-servicing-cost?memberId=1&shopName=dulal&serviceName=Full&serviceAmount=500&shopRatingByUser=5&shopReviewByUser=CHOLE AR KI&serviceTime=2018-09-13 18:41:45&shopLocation=kollanpur
    public function servicingCost(Request $request)
    {
      
      $shopAlreadyExist = $request->shopName;
      $memberId = $request->memberId;
      $shopName = $request->shopName;
      $serviceName = $request->serviceName;
      $serviceAmount = $request->serviceAmount;

      $shopReviewByUser = $request->shopReviewByUser;
      $serviceTime = $request->serviceTime;
      $shopLocation = $request->shopLocation;
      $shopRating = $request->shopRatingByUser;

      $validator = Validator::make($request->all(), [
          'memberId' => 'required | max:30| min:1',
          'shopName' => 'max:30 | required | min: 2',
          'serviceName' => 'max:90 | required',
          'serviceAmount' => ['required', 'regex:/^[1-9][0-9]+|not_in:0/', 'max:5', 'min:2'],
          'shopRatingByUser' => ['required','max:5', 'min:1'],
          'shopReviewByUser' => 'max:250 | required',
          'serviceTime' => 'max:30 | required| date',
          'shopLocation' => 'max:100 | required| min: 3',
      ]);

      if($validator->fails())
      {
          $data['status'] = '400';
          $data['message'] = "invalid input found, pass the data with desire format";
          $data['data'] =  $validator->errors();
      }
      else 
      {
        $validUser = $this->validUser($memberId);

        if ($validUser == true) // jodi user valid hoy
        {
          $shopAlreadyExistQuery = DB::table('shops')
                                    ->where('shop_name', 'like', "%{$shopAlreadyExist}%")
                                    ->get();

          $shopAlreadyExistrow = count($shopAlreadyExistQuery);
              
          if ($shopAlreadyExistrow > 0)//if shop already  exist in shop table
          {
            if ($memberId != " " && $memberId !=null
            && $shopName != " " && $shopName !=null
            && $serviceName != " " && $serviceName !=null
            && $serviceAmount != " " && $serviceAmount !=null
            && $shopRating != " "&& $shopRating !=null
            && $serviceTime != " " && $serviceTime !=null
            && $shopLocation != " " && $shopLocation !=null
            && $shopReviewByUser != " " && $shopReviewByUser !=null)//if all params are ok
            {
              $tus = new Temporary_user_service();
              $tus->member_id = $memberId;
              $tus->shop_name = $shopName;
              $tus->service_name = $serviceName;
              $tus->service_amount = $serviceAmount;
              $tus->shop_rating_by_user = $shopRating;
              $tus->shop_review_by_user = $shopReviewByUser;
              $tus->service_date_time = $serviceTime;
              $tus->shop_location = $shopLocation;
              $tus->created_at = $this->currentDateTime();
              $tus->updated_at = $this->currentDateTime();

              $tusInsertion = $tus->save();
              if ($tusInsertion == 1)//if insertion is successfull
              {
                $memberFinding = Member::where('member_id', $memberId)->first();
                $userYearlyCost =  $memberFinding->user_yearly_expenditure;
                $totalServicingCost = $serviceAmount + $userYearlyCost;

                //updating total yearly cost
                DB::table('members')
                ->where('member_id', $memberId)
                ->update(['user_yearly_expenditure' => $totalServicingCost]);

                $data['status'] = "200";
                $data['message'] = "user service insertion cost successfull";


              }
              else
              {
                $data['status'] = "400";
                $data['message'] = "user service cost insertion failed";
              }

            }
            else //if input invalid
            {
              $data['status'] = "400";
              $data['message'] = "insufficient input";
            }

          }
          else//if shop DOESNT not exist in shop table
          {
            if ($memberId != " " && $memberId !=null
            && $shopName != " " && $shopName !=null
            && $serviceName != " " && $serviceName !=null
            && $serviceAmount != " " && $serviceAmount !=null
            && $shopRating != " "&& $shopRating !=null
            && $serviceTime != " " && $serviceTime !=null
            && $shopLocation != " " && $shopLocation !=null
            && $shopReviewByUser != " " && $shopReviewByUser !=null)//if all params are ok
            {
              $tus = new Temporary_user_service();
              $tus->member_id = $memberId;
              $tus->shop_name = $shopName;
              $tus->service_name = $serviceName;
              $tus->service_amount = $serviceAmount;
              $tus->shop_rating_by_user = $shopRating;
              $tus->shop_review_by_user = $shopReviewByUser;
              $tus->service_date_time = $serviceTime;
              $tus->shop_location = $shopLocation;
              $tus->created_at = $this->currentDateTime();
              $tus->updated_at = $this->currentDateTime();

              $tusInsertion = $tus->save();

              $shop = new Shop();
              $shop['shop_name'] = $shopName;
              $shop['location'] = $shopLocation;

              $shopInsertion = $shop->save();


              if ($tusInsertion == 1 && $shopInsertion == 1)//if insertion is successfull
              {
                $memberFinding = Member::where('member_id', $memberId)->first();
                $userYearlyCost =  $memberFinding->user_yearly_expenditure;
                $totalServicingCost = $serviceAmount + $userYearlyCost;

                //updating total yearly cost
                DB::table('members')
                ->where('member_id', $memberId)
                ->update(['user_yearly_expenditure' => $totalServicingCost]);

                $data['status'] = "200";
                $data['message'] = "user service insertion cost successfull both tables";

              }
              else
              {
                $data['status'] = "400";
                $data['message'] = "user service cost insertion failed";
              }

            }
            else //if input invalid
            {
              $data['status'] = "400";
              $data['message'] = "insufficient input";
            }
          }

        }
        else 
        {
          $data['status'] = '400';
          $data['message'] = "user not found";
        }
        
      }
            
        
       //................insert details to Api_log starts......................................

            $params = array(
                        'memberId' => $memberId,
                        'shopName' => $shopName,
                        'serviceName' => $serviceName,
                        'shopRatingByUser' => $shopRating,
                        'shopReviewByUser' => $shopReviewByUser,
                        'serviceTime' => $serviceTime,
                        'shopLocation' => $shopLocation,
                        );

            $requestDetails = url()->current()."?".http_build_query($params);
            $responseDetails = response()->json($data);
            $apiName = "servicingCost";
            $apiReqType = "POST";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

      //................insert details to Api_log ends......................................


      $temporaryServiceresponse = json_encode($data, JSON_PRETTY_PRINT);
      return $temporaryServiceresponse;
    }


//...............................................Service Update..........................................................................................................................
// http://localhost:8000/service-update/36?memberId=1&shopName=dulal&serviceName=Full&serviceAmount=500&shopRatingByUser=5&shopReviewByUser=CHOLE AR KI&serviceTime=2018-09-13 18:41:45&shopLocation=kollanpur

  public function updateService(Request $request)
  {
    $serviceId = $request->serviceId;
    $memberId = $request->memberId;
    $shopName = $request->shopName;
    $serviceName = $request->serviceName;
    $serviceAmount = $request->serviceAmount;
    $shopRating = $request->shopRatingByUser;
    $shopReviewByUser = $request->shopReviewByUser;
    $serviceTime = $request->serviceTime;
    $shopLocation = $request->shopLocation;


      $validator = Validator::make($request->all(), [
          'memberId' => 'required | max:30| min:1',
          'serviceId' => 'required | max:30| min:1',
          'shopName' => 'max:30 | required | min: 2',
          'serviceName' => 'max:90 | required',
          'serviceAmount' => ['required', 'regex:/^[1-9][0-9]+|not_in:0/', 'max:5', 'min:2'],
          'shopRatingByUser' => ['required','max:5', 'min:1'],
          'shopReviewByUser' => 'max:250 | required',
          'serviceTime' => 'max:30 | required| date',
          'shopLocation' => 'max:100 | required| min: 3',
      ]);

      if($validator->fails())
      {
          $data['status'] = '400';
          $data['message'] = "invalid input found, pass the data with desire format";
          $data['data'] =  $validator->errors();
      }
      else 
      {
        $validUser = $this->validUser($memberId);

        if ($validUser == true) // jodi user valid hoy
        {   
          // SELECT * FROM `temporary_user_services` WHERE `temporary_user_services_id` = '10'
          $validServiceId = DB::table('temporary_user_services')
                            ->where('temporary_user_services_id','=',$serviceId)
                            ->get();

          $validServiceIdCount = count($validServiceId);            
          if ($validServiceIdCount > 0) 
          {
            $serviceData = DB::table('temporary_user_services')
              ->where('temporary_user_services_id','=',$serviceId)
              ->get();


            foreach ($serviceData as $key)
            {
              $perviousServiceAmount = $key->service_amount;
            }

            $userYearlyExpenditureMember = Member::where('member_id', $memberId)->get();
            foreach ($userYearlyExpenditureMember as $key)
            {
              $yearlyCost = $key->user_yearly_expenditure;
            }

            //update  temporary_user_services table
              $updateTust = DB::table('temporary_user_services')
                              ->where('temporary_user_services_id','=',$serviceId)
                              ->update([
                                        'member_id' => $memberId,
                                        'shop_name' => $shopName,
                                        'service_name' => $serviceName,
                                        'service_amount' => $serviceAmount,
                                        'shop_rating_by_user' => $shopRating,
                                        'shop_review_by_user' => $shopReviewByUser,
                                        'service_date_time' => $serviceTime,
                                        'shop_location' => $shopLocation,
                                        'updated_at' => $this->currentDateTime(),
                                      ]);

              //update memberTable
              if ($serviceAmount < $perviousServiceAmount)
              {
                $extraAmount = $perviousServiceAmount - $serviceAmount;
                $updatedYearlyAmount = $yearlyCost - $extraAmount;

                $umt = DB::table('members')
                                ->where('member_id','=',$memberId)
                                ->update(['user_yearly_expenditure' => $updatedYearlyAmount]);
              }
              else
              {
                $extraAmount = $serviceAmount - $perviousServiceAmount;
                $updatedYearlyAmount = $yearlyCost + $extraAmount;

                $umt = DB::table('members')
                                ->where('member_id','=',$memberId)
                                ->update([
                                            'user_yearly_expenditure' => $updatedYearlyAmount, 
                                            'updated_at' => $this->currentDateTime(), 
                                         ]);
              }


            //response
            // umt = update member table
            if ($umt == 1 && $updateTust == 1)
            {
              $data['status'] = 200;
              $data['message'] = "service updated successfully";
            }
            else
            {
              $data['status'] = 400;
              $data['message'] = "service update failed, Please try again later";
            }
          } 
          else 
          {
            $data['status'] = 400;
            $data['message'] = "wrong service id provided";
          }
          
        }
        else
        {
          $data['status'] = '400';
          $data['message'] = "user not found";
        }
      }


      //................insert details to Api_log starts......................................

            $params = array(
                        'memberId' => $memberId,
                        'shopName' => $shopName,
                        'serviceName' => $serviceName,
                        'shopRatingByUser' => $shopRating,
                        'shopReviewByUser' => $shopReviewByUser,
                        'serviceTime' => $serviceTime,
                        'shopLocation' => $shopLocation,
                        );

            $requestDetails = url()->current()."?".http_build_query($params);
            $responseDetails = response()->json($data);
            $apiName = "updateService";
            $apiReqType = "POST";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

      //................insert details to Api_log ends......................................


      $response = json_encode($data, JSON_PRETTY_PRINT);

      return $response;
  }



//...............................................Service Delete..........................................................................................................................
  // http://localhost:8000/api/service-delete
  public function deleteService(Request $request)
  {

    $serviceId = $request->serviceId;
    $memberId = $request->memberId;

    $validator = Validator::make($request->all(), [
        'memberId' => 'required | max:30| min:1',
        'serviceId' => 'required | max:30| min:1',
    ]);

    if($validator->fails())
    {
        $data['status'] = '400';
        $data['message'] = "invalid input found, pass the data with desire format";
        $data['data'] =  $validator->errors();
    }
    else 
    {
      $validUser = $this->validUser($memberId);
      
      if ($validUser == true) 
      {
        $validServiceId = DB::table('temporary_user_services')
                  ->where('temporary_user_services_id','=',$serviceId)
                  ->get();

        $validServiceIdCount = count($validServiceId);            
        if ($validServiceIdCount > 0) 
        {
          $serviceData = DB::table('temporary_user_services')
                        ->where('temporary_user_services_id','=',$serviceId)
                        ->get();

          foreach ($serviceData as $key)
          {
            $perviousServiceAmount = $key->service_amount;
          }

          $userYearlyExpenditureMember = Member::where('member_id', $memberId)->get();
          foreach ($userYearlyExpenditureMember as $key)
          {
            $yearlyCost = $key->user_yearly_expenditure;
          }

          $newAmountYearly = $yearlyCost - $perviousServiceAmount;

          $delete = DB::table('temporary_user_services')
                  ->where('temporary_user_services_id', '=', $serviceId)
                  ->delete();

          //update memberTable
            $umt = DB::table('members')
                            ->where('member_id','=',$memberId)
                            ->update(['user_yearly_expenditure' => $newAmountYearly]);
    

          if ($delete == 1)
          {
            $data['status'] = 200;
            $data['message'] = "service deleted successfully";
          }
          else
          {
            $data['status'] = 500;
            $data['message'] = "service not deleted, please try again";
          }
        }
        else
        {
            $data['status'] = 400;
            $data['message'] = "wrong service id provided";
        }
      } 
      else 
      {
        $data['status'] = 400;
        $data['message'] = "user not found";      
      }      
    }


      //................insert details to Api_log starts......................................

            $params = array(
                          'serviceId' => $serviceId,
                          'memberId' => $memberId,
                        );

            $requestDetails = url()->current()."?".http_build_query($params);
            $responseDetails = response()->json($data);
            $apiName = "deleteService";
            $apiReqType = "POST";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

      //................insert details to Api_log ends......................................

    $response = json_encode($data, JSON_PRETTY_PRINT);
    return $response;
  }
//...............................................Service Delete Ends..........................................................................................................................




    /** valid user or not */
    public function validUser($id)
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

    public function findMemberId(Request $request)
    {
       $fbId = $request->firebase_id;

        $validator = Validator::make($request->all(), [
          'firebase_id' => 'required | max:100',
        ]);

        if($validator->fails())
        {
            $data['status'] = '400';
            $data['message'] = "invalid input found, pass the data with desire format";
            $data['data'] =  $validator->errors();
        }
        else 
        {
            $fmq = DB::table('members')->select('member_id')->where('firebase_id', $fbId)->first();

            if ( count($fmq) > 0 ) 
            {         
              $data['status'] = 200;
              $data['message'] = "user found";
              $data['member_id'] = $fmq->member_id;
            } 
            else 
            {
              $data['status'] = 400;
              $data['message'] = "user not found";
            }        
         }
            

          //................insert details to Api_log starts......................................
            $params = array(
                          'firebase_id' => $fbId,
                          );

            $requestDetails = url()->current()."?".http_build_query($params);
            $responseDetails = response()->json($data);
            $apiName = "findMemberId";
            $apiReqType = "GET";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

          //................insert details to Api_log ends......................................



       $response = json_encode($data, JSON_PRETTY_PRINT); 
       return $response;      
    }


    /**............................. All service list starts............................................ */

    public function allServiceList(Request $request)
    {
        $userId = $request->userId;

        $validator = Validator::make($request->all(), [
          'userId' => 'required | max:30| min:1',
        ]);

        if ($validator->fails()) 
        {
          $data['status'] = '400';
          $data['message'] = "invalid input found, pass the data with desire format";
          $data['data'] =  $validator->errors();
        } 
        else 
        {
          $validUser = $this->validUser($userId);

          if ($validUser == true)
          {            
            $serviceData = DB::table('temporary_user_services')
                                    ->select('temporary_user_services_id as service_id','shop_name', 'service_name', 'service_amount', 'service_date_time')
                                    ->where('member_id', $userId)
                                    ->get();

            $serviceDataResult = count($serviceData);
            
            if ($serviceDataResult > 0) 
            {
               $data['status'] = '200';
               $data['message'] = "Services found";
               $data['data'] = $serviceData;
            } 
            else 
            {
               $data['status'] = '201';
               $data['message'] = "No Services found agaisnt you";
            }            
          }
          else 
          {
            $data['status'] = '400';
            $data['message'] = "user not found";
          }
        }

        
          //................insert details to Api_log starts......................................
            $params = array(
                          'userId' => $userId,
                          );

            $requestDetails = url()->current()."?".http_build_query($params);
            $responseDetails = response()->json($data);
            $apiName = "allServiceList";
            $apiReqType = "GET";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLog($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

          //................insert details to Api_log ends......................................

        $response = json_encode($data, JSON_PRETTY_PRINT); 
       return $response;                    
        
    }

    /**............................. All service list ends............................................ */


}
