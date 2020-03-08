<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|alpha|max:50',
            'last_name' => 'required|alpha|max:50',
            'user_email' => 'required|email|unique:login_users,user_email',
            'phone' => 'required|numeric|digits_between:10,16',
            'user_password' => 'required|min:6',
            'c_password' => 'required|same:user_password',
        ];
    }

    public function attributes()
    {
        return [

            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'user_email' => 'Email Address',
            'phone' => 'Phone',
            'user_password' => 'Password',
            'c_password' => 'Password Confirmation',
        ];
    }


}
