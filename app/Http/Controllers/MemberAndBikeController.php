<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ExistTrait;
use App\Bike;
use App\Member;
use App\Motorcycle_manufacturer;
use Validator;
use Illuminate\Support\Facades\DB;


class MemberAndBikeController extends Controller
{
    use ExistTrait;

    public function memberBikeStore(Request $request)
    {
       $userId = $request->userId;
       $bikeId = $request->bikeId;
       $modelYear = $request->modelYear;
      
       $validator = Validator::make($request->all(), [
            'userId' => 'required | max:30| min:1',
            'bikeId' => 'required | max:30| min:1',
            'modelYear' => 'required | max:4| min:4',
        ]);
        
        if($validator->fails())
        {
            $data['status'] = '400';
            $data['message'] = "invalid mode found, pass the data with desire format";
            $data['data'] =  $validator->errors();
        }
        else 
        {
            $userExist = $this->validUserExistsTrait($userId);
            $bikeExist = $this->allBikeListExistsTrait($bikeId);

            if ($userExist == true && $bikeExist == true) 
            {
                // member_id` `bike_list_id` `model_year` `created_at` `updated_at` 
                
                $bike = new Bike();

                $bike->member_id = $userId;
                $bike->bike_list_id = $userId;
                $bike->model_year = $modelYear;
                $bike->created_at = $this->currentDateTimeExistTrait();
                $bike->updated_at = $this->currentDateTimeExistTrait();

                $storeSuccess = $bike->save();

                if ($storeSuccess == 1) 
                {
                    $data['status'] = '200';
                    $data['message'] = 'Stored Successfully';            
                } 
                else 
                {
                    $data['status'] = '500';
                    $data['message'] = 'Stored failed, try again later';
                }
            } 
            else 
            {
                $data['status'] = '400';
                $data['message'] = 'user not found or this bike is not available in our system, please try again later';            
            }
        }
        
    //................insert details to Api_log starts......................................
        $params = array(
                        'userId' => $userId,
                        'bikeId' => $bikeId,
                        'modelYear' => $modelYear,
                    );

        $requestDetails = url()->current()."?".http_build_query($params);
        $responseDetails = response()->json($data);
        $apiName = "memberBikeStore";
        $apiReqType = "POST";
        $clientIp =  $request->ip();
        $currentUrl = $request->url();

        $this->insertApiLogExistTrait($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

      //................insert details to Api_log ends......................................

        $response =  json_encode($data, JSON_PRETTY_PRINT);
        return $response;
       
    }



    /** Bike information of a specific user */
    public function information(Request $request)
    {
       $userId = $request->userId;
      
       $validator = Validator::make($request->all(), [
            'userId' => 'required | max:30| min:1',
        ]);
        
        if($validator->fails())
        {
            $data['status'] = '400';
            $data['message'] = "invalid mode found, pass the data with desire format";
            $data['data'] =  $validator->errors();
        }
        else 
        {
            $userExist = $this->validUserExistsTrait($userId);

            if ($userExist == true) 
            {
                // SELECT a.bike_list_id AS user_bike_id, bike_name, model_year, company_name, a.member_id
                // FROM bikes AS a
                // LEFT JOIN all_bike_lists AS b
                // ON a.bike_list_id = b.bike_list_id
                // LEFT JOIN motorcycle_manufacturers AS c
                // ON b.manufacturer_id = c.manufacturer_id
                // WHERE a.member_id = '4'
                $bikeInfo = DB::table('bikes')
                                    ->select('id','a.bike_list_id', 'bike_name', 'model_year', 'company_name', 'a.member_id')
                                    ->from('bikes as a')
                                    ->leftJoin('all_bike_lists AS b', 'a.bike_list_id', '=', 'b.bike_list_id')
                                    ->leftJoin('motorcycle_manufacturers AS c', 'b.manufacturer_id', '=', 'c.manufacturer_id')
                                    ->where('a.member_id', '=', $userId)
                                    ->where('a.status', '=', 1)
                                    ->get();

                $bikeInfoRow = count($bikeInfo);

                if ($bikeInfoRow > 0) 
                {
                   $data['status'] = 200;
                   $data['data'] = $bikeInfo;
                } 
                else 
                {
                    $data['status'] = 201;
                    $data['message'] = "no data found";
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
            $apiName = "memberBikeInformation";
            $apiReqType = "GET";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLogExistTrait($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

        //................insert details to Api_log ends......................................

        $response =  json_encode($data, JSON_PRETTY_PRINT);
        return $response;
    }

    /** Bike information update */
    public function updateBikeInfo(Request $request)
    {
       $userId = $request->userId;
       $updateBikeId = $request->updateBikeInfoId;
       $bikeListId = $request->bikeListId;
       $modelYear = $request->modelYear;
       
        $validator = Validator::make($request->all(), [
            'updateBikeInfoId' => 'required | max:30| min:1',
            'bikeListId' => 'required | max:30| min:1',
            'modelYear' => 'required | max:4| min:4',
        ]);
        
        if($validator->fails())
        {
            $data['status'] = '400';
            $data['message'] = "invalid mode found, pass the data with desire format";
            $data['data'] =  $validator->errors();
        }
        else 
        {
            // SELECT id FROM bikes WHERE id = 'a'

            $bike = DB::table('bikes')
                            ->where('id', $updateBikeId)
                            ->where('status', 1)
                            ->first();

            $bikeRow = count($bike);

            if ($bikeRow > 0) 
            {
               $bikeExist = $this->allBikeListExistsTrait($bikeListId);

               if ($bikeExist == true) 
               {
                  $updateBikeInfo = DB::table('bikes')
                                        ->where('id', $updateBikeId)
                                        ->update([
                                            'bike_list_id' => $bikeListId,
                                            'model_year' => $modelYear,
                                            'updated_at' => $this->currentDateTimeExistTrait(),
                                        ]);

                   if ($updateBikeInfo == true) 
                   {
                        $data['status'] = '200';
                        $data['message'] = "information updated successfully";
                   } 
                   else 
                   {
                        $data['status'] = '500';
                        $data['message'] = "information updated failed, please try again later";                   
                   }
                                            
               } 
               else 
               {
                    $data['status'] = '400';
                    $data['message'] = "this bike doesn't exist in our system";
               }
               
            } 
            else 
            {
                $data['status'] = '400';
                $data['message'] = "nothing to update, because no bike is registered against you";
            }
            
        }

        //................insert details to Api_log starts......................................
            $params = array(
                            'userId' => $userId,
                            'updateBikeInfoId' => $updateBikeId,
                            'bikeListId' => $bikeListId,
                            'modelYear' => $modelYear,
                        );

            $requestDetails = url()->current()."?".http_build_query($params);
            $responseDetails = response()->json($data);
            $apiName = "updateBikeInfo";
            $apiReqType = "PUT";
            $clientIp =  $request->ip();
            $currentUrl = $request->url();

            $this->insertApiLogExistTrait($requestDetails, $responseDetails, $apiName, $apiReqType, $clientIp, $currentUrl);

        //................insert details to Api_log ends......................................

        $response =  json_encode($data, JSON_PRETTY_PRINT);
        return $response;
    }
}
