<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    
    public function index(){
        $permissions = Permission::paginate('10');
        if(empty($permissions)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        return response()->json(['permissions' => $permissions], 200);
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
        
        $permission = new Permission();
        $permission->name = $request->name;
        $permission->guard_name = 'api';
        $permission->save();
  
        return response()->json(['permission' => $permission], 201);
    }

    public function show($id){
        $permission = Permission::find($id);
        if(empty($permission)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        return response()->json(['permission' => $permission], 201);
    }
    public function update(Request $request, $id){

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
  
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $permission = Permission::find($id);
        if(empty($permission)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        $permission->name = $request->name;
        $permission->guard_name = 'api';
        $permission->save();
  
        return response()->json(['permission' => $permission], 201);
       
    }

    public function destroy($id){
        $permission = Permission::find($id);
        if(empty($permission)){
            return response()->json(['message' => 'Record not found'], 400);    
        }
        $permission->delete();

        return response()->json(['permission' => $permission], 201);

    }

}
