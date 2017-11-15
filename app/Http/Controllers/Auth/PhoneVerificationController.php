<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Library\Services\PhoneVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Exception;

class PhoneVerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Phone Verification  Controller
    |--------------------------------------------------------------------------
    |
    | Uses Authy to verify a users phone via voice or sms.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming verification request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function verificationRequestValidator(array $data)
    {
        return Validator::make($data, [
            'country_code' => 'required|string|max:3',
            'phone_number' => 'required|string|max:10',
            'via' => 'required|string|max:4',
        ]);
    }

    /**
     * Get a validator for an code verification request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function verificationCodeValidator(array $data)
    {
        return Validator::make($data, [
            'country_code' => 'required|string|max:3',
            'phone_number' => 'required|string|max:10',
            'token' => 'required|string|max:4'
        ]);
    }

    /**
     * Request phone verification via PhoneVerificationService.
     *
     * @param  array  $data
     * @return Illuminate\Support\Facades\Response;
     */
    protected function startVerification(
        Request $request,
        PhoneVerification $PhoneVerification
    ) {
        $data = $request->all();
        $validator = $this->verificationRequestValidator($data);

        if ($validator->passes()) {
            return $PhoneVerification->requestVerification($data);
        }

        return response()->json(['errors'=>$validator->errors()], 403);
    }

    /**
     * Request phone verification via PhoneVerificationService.
     *
     * @param  array  $data
     * @return Illuminate\Support\Facades\Response;
     */
    protected function verifyCode(
        Request $request,
        PhoneVerification $PhoneVerification
    ) {
        $data = $request->all();
        $validator = $this->verificationCodeValidator($data);

        if ($validator->passes()) {
            try {
                $result =  $PhoneVerification->verifyToken($data);
                return response()->json($result, 200);
            } catch (Exception $e) {
                $response=[];
                $response['exception'] = get_class($e);
                $response['message'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
                return response()->json($response, 403);
            }
        }

        return response()->json(['errors'=>$validator->errors()], 403);
    }
}
