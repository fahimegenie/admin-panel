<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * @var array
     */
    protected $response = [];
    protected $status = 200;
    
    public function index(){
        $permissions = Permission::paginate('10');
        if(empty($permissions)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);   
        }
        $this->response['message'] = 'Permissions list';
        $this->response['data'] = $permissions;
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
    
        if ($validator->fails()) {
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }
        
        $permission = new Permission();
        $permission->name = $request->name;
        $permission->guard_name = 'api';
        $permission->save();
        
        $this->response['message'] = 'Permission created successfully!';
        $this->response['permission'] = $permission;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    public function detail($permission_id){
        $permission = Permission::find($permission_id);
        if(empty($permission)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $this->response['message'] = 'Permission details!';
        $this->response['data'] = $permission;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
        return response()->json(['permission' => $permission], 201);
    }
    public function update(Request $request, $permission_id){

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
        
        if ($validator->fails()) {
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }
        
        $permission = Permission::find($permission_id);
        if(empty($permission)){
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);   
        }
        $permission->name = $request->name;
        $permission->guard_name = 'api';
        $permission->save();
        
        $this->response['message'] = 'Permission updated successfully!';
        $this->response['data'] = $permission;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

       
    }

    public function destroy($permission_id){
        $permission = Permission::find($permission_id);
        if(empty($permission)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);   
        }
        $permission->delete();

        $this->response['message'] = 'Permission deleted successfully!';
        $this->response['data'] = $permission;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

}
