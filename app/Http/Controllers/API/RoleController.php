<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{

     /**
     * @var array
     */
    protected $response = [];
    protected $status = 200;
    
    
    public function index(){
        $roles = Role::paginate('10');
        if(empty($roles)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);    
        }
        $this->response['message'] = 'Roles list';
        $this->response['data'] = $roles;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
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
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }
        
        $role = new Role();
        $role->name = $request->name;
        $role->guard_name = 'api';
        $role->save();
    
        $this->response['message'] = 'Role created successfully!';
        $this->response['data'] = $role;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    public function detail($role_id){
        $role = Role::find($role_id);
        if(empty($role)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $this->response['message'] = 'Role details!';
        $this->response['data'] = $role;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }
    public function update(Request $request, $role_id){

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }
        
        $role = Role::find($role_id);
        if(empty($role)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $role->name = $request->name;
        $role->guard_name = 'api';
        $role->save();
        
        $this->response['message'] = 'Role updated successfully!';
        $this->response['data'] = $role;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    public function destroy($role_id){
        $role = Role::find($role_id);
        if(empty($role)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);   
        }
        $role->delete();

        $this->response['message'] = 'Role deleted successfully!';
        $this->response['data'] = $role;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
}
