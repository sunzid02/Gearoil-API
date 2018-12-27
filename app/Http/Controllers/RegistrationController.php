<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Member;
use App\Bike;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    /*
    member = `member_id` `username` `userrole` `email` `password` `user_monthly_expenditure` `user_yearly_expenditure` `created_at`
    bike = `company_name` `bike_name` `model_year` `member_id`
    */

    // http://localhost:8000/api/bike-registration?memberId=1&username=rahat&userRole=1&email=sun@mon.bd&password=123&companyName=suzuki&bikeName=pul&modelYear=2016

    public function bikeRegistration(Request $req)
    {
      //member tab info
      $memberId = $req->memberId;
      $username = $req->username;
      $userRole = $req->userRole;
      $email = $req->email;
      $userMonthlyExpenditure = 0;
      $userYearlyExpenditure = 0;
      $insertionTime =  Carbon::now()->setTimezone('Asia/Dhaka');

      //bike tab info
      $companyName = $req->companyName;
      $bikeName = $req->bikeName;
      $modelYear = $req->modelYear;

      if ($memberId != " " && $memberId !=null
      && $username != " " && $username !=null
      && $userRole != " " && $userRole !=null
      && $email != " " && $email !=null
      && $companyName != " "&& $companyName !=null
      && $bikeName != " " && $bikeName !=null
      && $modelYear != " " && $modelYear !=null)
      {
        $member = new Member();

        $member->member_id = $memberId;
        $member->username = $username;
        $member->userrole = $userRole;
        $member->email = $email;
        $member->user_monthly_expenditure = $userMonthlyExpenditure;
        $member->user_yearly_expenditure = $userYearlyExpenditure;
        $member->created_at = $insertionTime;

        $tab1Insertion = $member->save();

        // $something->users()->save($user);
        $bike = new Bike();
        $bike['company_name'] = $companyName;
        $bike['bike_name'] = $bikeName;
        $bike['model_year'] = $modelYear;
        $bike['member_id'] = $memberId;

        $tab1Insertion2 = $bike->save();

        if ($tab1Insertion == 1 && $tab1Insertion2 == 2)
        {
          $data['status'] = "200";
          $data['message'] = "insertion successfull";
        }
        else
        {
          $data['status'] = "300";
          $data['message'] = "insertion unsuccessfull";
        }

      }
      else
      {
        $data['status'] = "300";
        $data['message'] = "insufficient input";
      }

      $response = json_encode($data, JSON_PRETTY_PRINT);
      return $response;
    }
}
