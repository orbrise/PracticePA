<?php

namespace App\Http\Controllers\Api\v1\ProfileController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\CompanyConfig;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\UserRequest;
use App\Http\Resources\User as UserResource;
use Arr;
use App\OauthClient;
use App\Module;
use App\Http\Resources\Module\ModuleCollection;
use App\Http\Resources\Module\ModuleResource;
use App\ClientType;
use App\ServiceType;
use App\Client;
use App\Country;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\CompanyConfig\CompanyConfigResource;
use App\Http\Resources\Profile\ProfileResource;
use DB;
use App\LoginUserPermission;
use App\LoginPermission;


class ProfileController extends Controller
{
    public function EditProfile(Request $req)
{
    /*$bearerToken = $req->bearerToken();
    $tokenId = (new \Lcobucci\JWT\Parser())->parse($bearerToken)->getHeader('jti');
    $client = \Laravel\Passport\Token::find($tokenId)->client;

    $client_id = $client->id;
    $client_secret = $client->secret;*/ 
    $user_data  = User::where(['user_id'=>$req->user_id])->first();
    if($user_data)
    {
        $return_data['FirstName'] = $user_data->first_name;
        $return_data['LastName'] = $user_data->last_name;
        $return_data['Email'] = $user_data->user_email;
        $return_data['Phone'] = $user_data->phone;
        $return_data['City'] = $user_data->city;
        $return_data['County'] = $user_data->county;
        $return_data['PostalCode'] = $user_data->postal_code;
        $return_data['Country'] = $user_data->country;
        $return_data['MinutesPerunit'] = CompanyConfig::company_data($user_data->company_id);
        return Response::json(['status' => 'success', 'data' => $return_data]);
    } else 
    {
       $errors['ErrorMessage'] = ['User Does Not Exist'];
       return Response::json(['status' => 'error', 'data' => $errors]);
    }
   return Response::json(['status' => 'success', 'data' => $data]);
}

public function UpdateProfile(Request $request)
{

    $FilterRequest = ['first_name', 'last_name', 'phone','city','county','postal_code','country'];
    $data = $request->only($FilterRequest);
        // changing the input fields name
    $attributes = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'phone' => 'Phone',
        'city' => 'City Name',
        'county' => 'County Name',
        'postal_code' => 'Postal code',
        'country' => 'Country',
    ];
    $validator = Validator::make($data ,[
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'phone' => 'required|numeric|digits_between:10,16',
        'city' => 'required|string|max:50',
        'county' => 'required|min:6',
        'postal_code' => 'required|numeric',
        'country' => 'required|string|max:50',
    ])->setAttributeNames($attributes);
    if($validator->fails())
    {
        return Response::json(['status' => 'error', 'data' => $validator->errors()]);
    }

    $user_data['first_name'] = $request->first_name;
    $user_data['last_name'] = $request->last_name;
    $user_data['phone'] = $request->phone;
    $user_data['city'] = $request->city;
    $user_data['county'] = $request->county;
    $user_data['postal_code'] = $request->postal_code;
    $user_data['country'] = $request->country;
    $updated_data = User::where(['user_id'=>$request->user_id])->update($user_data);
 
    if($updated_data>0 )
    {
        $success['SuccessMessage'] = ['Record Updated Successfully'];
        return Response::json(['status' => 'success', 'data' => $success]);

    }
    else
    {
        $errors['ErrorMessage'] = ['Record Not Updated'];
        return Response::json(['status' => 'error', 'data' => $errors]);

    }
    
}

}
