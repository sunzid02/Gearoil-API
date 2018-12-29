<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ExistTrait;
use App\Bike;
use Validator;

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
}
