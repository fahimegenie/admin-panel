<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\ApiController;
use App\Models\User;
use App\Traits\ImageStorageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{

    use ImageStorageTrait;
    

     /**
     * @var array
     */
    protected $response = [];
    protected $status = 200;

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'mobile_number' => 'required',
            'profile_pic' => 'required'
        ]);
    
        if ($validator->fails()) {
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }
        
        $user = new User();
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->username = $request->first_name.' '.$request->last_name;
        $user->mobile_number = $request->mobile_number;
        $image_name = '';
        if($request->hasFile('profile_pic')){
            $picture = $request->file('profile_pic');
            $folder = 'uploads'; 
            $image_name = $this->storeImage($picture, $folder);
        }
        $user->profile_pic = $image_name;
        $user->save();

        $this->response['message'] = 'User created successfully';
        $this->response['data'] = $user;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
  
  
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        $token = auth()->attempt($credentials);

        if (!$token) {
            $this->status = 401;
            $this->response['success'] = false;
            $this->response['message'] = 'Invalid user or password'; //'Unauthorized';
            $this->response['status'] = $this->status;
            return response()->json($this->response, $this->status);
        }

        $user = Auth::user();

        $token = $this->respondWithToken($token);

        $data['user'] = $user;
        $data['user']['token'] = $token;
        
        $this->response['success'] = true;
        $this->response['data'] = $data;
        $this->response['message'] = 'User login successfully';
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
  
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }
  
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
  
        return response()->json(['message' => 'Successfully logged out']);
    }
  
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
  
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return $token;
        $user = auth()->user();
        $user->access_token = $token;
        $user->token_type = 'bearer';
        $user->expires_in = auth()->factory()->getTTL() * 60;
        return response()->json([$user]);
    }
}
