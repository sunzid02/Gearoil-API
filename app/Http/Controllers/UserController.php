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
                ->get();

                // die();

          $row = count($monthlyCostAllUsers);

          if ($row > 0)
          {
            $data['status'] = 200;
            $data['currentMonth'] = $currentMonthName;
            $data['data'] = $monthlyCostAllUsers;
          }
          else
          {
            $data['status'] = 400;
            $data['currentMonth'] = $currentMonthName;
            $data['message'] = "no data found for this month";
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




/*.................................User Servicing insertion...............................................................................*/

// http://localhost:8000/api/user-servicing-cost?memberId=1&shopName=dulal&serviceName=Full&serviceAmount=500&shopRatingByUser=5&shopReviewByUser=CHOLE AR KI&serviceTime=2018-09-13 18:41:45&shopLocation=kollanpur
    public function servicingCost(Request $req)
    {
      $shopAlreadyExist = $req->shopName;
      $shopAlreadyExistQuery = DB::table('shops')
                ->where('shop_name', 'like', "%{$shopAlreadyExist}%")
                ->get();

      $shopAlreadyExistrow = count($shopAlreadyExistQuery);
      $memberId = $req->memberId;
      $shopName = $req->shopName;
      $serviceName = $req->serviceName;
      $serviceAmount = $req->serviceAmount;
      $shopRating = $req->shopRatingByUser;
      $shopReviewByUser = $req->shopReviewByUser;
      $serviceTime = $req->serviceTime;
      $shopLocation = $req->shopLocation;

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
          $tusInsertion = $tus->save();

          $shop = new Shop();
          $shop['shop_name'] = $shopName;
          $shop['location'] = $shopLocation;
          $shop['rating'] = $shopRating;

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

      $temporaryServiceresponse = json_encode($data, JSON_PRETTY_PRINT);
      return $temporaryServiceresponse;
    }


//...............................................Service Update..........................................................................................................................
// http://localhost:8000/service-update/36?memberId=1&shopName=dulal&serviceName=Full&serviceAmount=500&shopRatingByUser=5&shopReviewByUser=CHOLE AR KI&serviceTime=2018-09-13 18:41:45&shopLocation=kollanpur

  public function updateService(Request $req)
  {
    $serviceId = $req->serviceId;
    $memberId = $req->memberId;
    $shopName = $req->shopName;
    $serviceName = $req->serviceName;
    $serviceAmount = $req->serviceAmount;
    $shopRating = $req->shopRatingByUser;
    $shopReviewByUser = $req->shopReviewByUser;
    $serviceTime = $req->serviceTime;
    $shopLocation = $req->shopLocation;

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
                        ->update(['user_yearly_expenditure' => $updatedYearlyAmount]);
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
        $data['status'] = 300;
        $data['message'] = "service update failed, Please try again later";
      }

      $response = json_encode($data, JSON_PRETTY_PRINT);

      return $response;
  }




  //...............................................Service Delete..........................................................................................................................
  // http://localhost:8000/api/service-delete/58
  public function deleteService(Request $req)
  {
    $id = $req->id;

    $delete = DB::table('temporary_user_services')
                    ->where('temporary_user_services_id', '=', $id)
                    ->delete();

    if ($delete == 1)
    {
      $data['status'] = 200;
      $data['message'] = "service deleted successfully";
    }
    else
    {
      $data['status'] = 300;
      $data['message'] = "service not deleted, please try again";
    }

    $response = json_encode($data, JSON_PRETTY_PRINT);

    return $response;
  }



}
