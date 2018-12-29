<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Member;
use App\Api_log;
use App\Motorcycle_manufacturer;

use Validator;


class MotorcycleController extends Controller
{
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

    /** motorcycle company list */
    public function allMotorCycleCompanyList(Request $request)
    {
        $userId = $request->userId;
        $mode = $request->mode;

        $validator = Validator::make($request->all(), [
          'userId' => 'required | max:30| min:1',
          'mode' => 'max:30 | required | min: 2',
        ]);

        if($validator->fails())
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
                if( $mode == "onlyManufacturers")
                {
                    $manufacturers = DB::table('motorcycle_manufacturers')
                                            ->select('manufacturer_id', 'company_name')
                                            ->get();
                    
                    $manufacturersRow = count($manufacturers);  
                    $data = ($manufacturersRow > 0) ?  array('status' => '200' , 'data' =>$manufacturers ) : array('status' => '200' , 'message' => "no data found, please try again later" ) ;                      
                                     
                }
                elseif ( $mode == "manufacturersAndBikes") 
                {       
                    $manufacturerId = $request->manufacturerId;

                    $validator = Validator::make($request->all(), [
                        'manufacturerId' => 'required | max:30| min:1',
                    ]);
                    
                    if($validator->fails())
                    {
                        $data['status'] = '400';
                        $data['message'] = "invalid mode found, pass the data with desire format";
                        $data['data'] =  $validator->errors();
                    }
                    else 
                    {
                        $bikeList = DB::table('all_bike_lists')
                                        ->select('manufacturer_id','bike_list_id', 'bike_name')
                                        ->where('manufacturer_id', $manufacturerId)
                                        ->get();
                        
                        $bikeListRow = count($bikeList);

                        $data = ($bikeListRow > 0) ?  array('status' => '200' , 'data' =>$bikeList ) : array('status' => '200' , 'message' => "no data found, please try again later" ) ;                      
                    }                   
                }
                else 
                {
                    $data['status'] = '400';
                    $data['message'] = "Invalid format";                    
                }
            }
            else 
            {
                $data['status'] = '400';
                $data['message'] = "user not found";           
            }
            
        }
      
      $response = json_encode($data, JSON_PRETTY_PRINT);
      return $response;
    }
}
