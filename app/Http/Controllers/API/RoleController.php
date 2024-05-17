<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    
    public function index(){
        $roles = Role::paginate('10');
        if(empty($roles)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        return response()->json(['roles' => $roles], 200);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
  
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $role = new Role();
        $role->name = $request->name;
        $role->guard_name = 'api';
        $role->save();
  
        return response()->json(['role' => $role], 201);
    }

    public function show($id){
        $role = Role::find($id);
        if(empty($role)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        return response()->json(['role' => $role], 201);
    }
    public function update(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
  
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $role = Role::find($id);
        if(empty($role)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        $role->name = $request->name;
        $role->guard_name = 'api';
        $role->save();
  
        return response()->json(['role' => $role], 201);
       
    }

    public function destroy($id){
        $role = Role::find($id);
        if(empty($role)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        $role->delete();

        return response()->json(['role' => $role], 201);

    }
}
