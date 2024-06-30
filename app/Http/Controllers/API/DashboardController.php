<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PatientCase;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    
    /**
     * @var array
     */
    protected $response = [];
    protected $status = 200;
    protected $user_id = 0;
    protected $role_name = '';
    public function __construct(){
        if(!empty(auth()->user())){
            $this->user_id = auth()->user()->id;
            $this->role_name = auth()->user()->role_name;
        }
    }

    public function adminDashboard(){

        $patient_cases = [];
            $patient_cases = User::withCount(['created_user', 'users', 'planner', 'qa', 'post_processing'])->where('id', auth()->user()->id)->first();
            $cases_count = PatientCase::count();
        $patient_cases['cases_count'] = $cases_count;
        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $this->response['message'] = 'Patient cases list!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
}
