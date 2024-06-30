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

        // $startWeek = Carbon::now()->subWeek()->startOfWeek(); // 30 May 2024
        // $endWeek   = Carbon::now()->subWeek()->endOfWeek();  // 5 June 2024


        $patient_cases = [];
        $patient_cases = User::withCount(['created_user', 'users', 'planner', 'qa', 'post_processing'])->where('id', auth()->user()->id)->first();
        

        $patient_cases['cases_count'] = PatientCase::count();
        $patient_cases['cases_0_count'] = PatientCase::where('status', 0)->count();
        $patient_cases['cases_1_count'] = PatientCase::where('status', 1)->count();
        $patient_cases['cases_2_count'] = PatientCase::where('status', 2)->count();
        $patient_cases['cases_3_count'] = PatientCase::where('status', 3)->count();
        $patient_cases['cases_4_count'] = PatientCase::where('status', 4)->count();
        $patient_cases['cases_5_count'] = PatientCase::where('status', 5)->count();
        $patient_cases['cases_6_count'] = PatientCase::where('status', 6)->count();
        $patient_cases['cases_7_count'] = PatientCase::where('status', 7)->count();
        $patient_cases['cases_8_count'] = PatientCase::where('status', 8)->count();
        $patient_cases['cases_9_count'] = PatientCase::where('status', 9)->count();
        $patient_cases['cases_10_count'] = PatientCase::where('status', 10)->count();
        $patient_cases['cases_11_count'] = PatientCase::where('status', 11)->count();
        $patient_cases['cases_12_count'] = PatientCase::where('status', 12)->count();
        

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
