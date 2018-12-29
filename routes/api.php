<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['GearOilAuthApi']], function () {

    Route::post('/bike-registration', 'RegistrationController@bikeRegistration')->name('registration.bikeRegistration');
    Route::get('/current-month-cost', 'UserController@currentMonthCost')->name('user.currentMonthCost');

    //add service
    Route::post('/user-servicing-cost', 'UserController@servicingCost' )->name('user.servicingCost');

    //update service
    Route::post('/service-update', 'UserController@updateService')->name('user.updateService');


    //Delete Service
    Route::post('/service-delete', 'UserController@deleteService')->name('user.deleteService');


    //service list
    Route::get('/all-service-list', 'UserController@allServiceList')->name('user.allServiceList');


    Route::group(['prefix'=>'motorcycle'],function(){
         Route::get('/all-motorcycle-company-list', 'MotorcycleController@allMotorCycleCompanyList')->name('motorcycle.allMotorCycleCompanyList');
    });


    Route::group(['prefix'=>'member-bike'],function(){
         Route::post('/store', 'MemberAndBikeController@memberBikeStore')->name('memberAndBike.memberBikeStore');
    });

    Route::resources([
        'members' => 'MemberController',
    ]);

    //find memberId in this server against firebaseId
    Route::get('/find-member-id', 'UserController@findMemberId')->name('user.findMemberId');


});



//generate token
Route::get('/get-token', 'TokenController@getTokenNow')->name('token.getTokenNow');

//wrong token
Route::get('/wrong-token', 'TokenController@wrongToken')->name('token.wrongToken');

//expire token
Route::get('/expire-token', 'TokenController@expireToken')->name('token.expireToken');




//................insert details to Api_log starts......................................

  // $params = array(
  //                'userId' => $userId,
  //                'authenticatedId' => $authenticatedId,
  //               );
  //
  // $requestDetails = url()->current()."?".http_build_query($params);
  // $responseDetails = response()->json($data);
  // $clientIp =  $request->ip();
  // $currentUrl = $request->url();
  // date_default_timezone_set("Asia/Dhaka");
  // $currentDateTime = date("Y-m-d h:i:s");
  //
  // $apiLog = new Api_log();
  //
  //
  // $apiLog->request_details = $requestDetails;
  // $apiLog->response_details = $responseDetails;
  // $apiLog->hitting_time = $currentDateTime;
  // $apiLog->request_type = "GET";
  // $apiLog->client_ip = $clientIp;
  // $apiLog->api_name = "profileCompleteStatus";
  //
  //
  // $apiLog->save();

//................insert details to Api_log ends......................................
