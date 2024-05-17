<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ImageStorageTrait;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ImageStorageTrait;
    
    public function index(){
        $users = User::paginate('10');
        if(empty($users)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        return response()->json(['users' => $users], 200);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'mobile_number' => 'required',
            'profile_pic' => 'required'
        ]);
  
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
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
  
        return response()->json(['user' => $user], 201);
    }

    public function show($id){
        $user = User::find($id);
        if(empty($user)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        return response()->json(['user' => $user], 201);
    }
    public function update(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'mobile_number' => 'required',
            'profile_pic' => 'required'
        ]);
  
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $user = User::find($id);
        if(empty($user)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->username = $request->first_name.' '.$request->last_name;
        $user->mobile_number = $request->mobile_number;
        $image_name = $user->profile_pic;
        if($request->hasFile('profile_pic')){
            $picture = $request->file('profile_pic');
            $folder = 'uploads'; 
            $image_name = $this->storeImage($picture, $folder);
        }
        $user->profile_pic = $image_name;

        $user->save();
  
        return response()->json(['user' => $user], 201);
       
    }

    public function destroy($id){
        $user = User::find($id);
        if(empty($user)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        $user->delete();

        return response()->json(['user' => $user], 201);

    }
}
