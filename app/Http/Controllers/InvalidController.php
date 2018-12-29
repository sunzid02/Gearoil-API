<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvalidController extends Controller
{
    public function invalidUser()
    {
      $data['status'] = 400;
      $data['message'] = "user not found"; 
      
      $response = json_encode($data, JSON_PRETTY_PRINT);
      return $response;
    }

    public function insuffInput()
    {
      $data['status'] = 400;
      $data['message'] = "insufficient input"; 
      
      $response = json_encode($data, JSON_PRETTY_PRINT);
      return $response;
    }
}
