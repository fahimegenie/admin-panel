<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PatientCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $start_date = date('Y-m-d');  

        $date_d = strtotime($start_date);
        $date_d = strtotime("-1 day", $date_d);
        $end_date_d = date('Y-m-d', $date_d);

        $date = strtotime($start_date);
        $date = strtotime("-7 day", $date);
        $end_date = date('Y-m-d', $date);

        $date_m = strtotime($start_date);
        $date_m = strtotime("-30 day", $date_m);
        $end_date_m = date('Y-m-d', $date_m);

        $date_y = strtotime($start_date);
        $date_y = strtotime("-365 day", $date_y);
        $end_date_y = date('Y-m-d', $date_y);

        $daily_date = [$end_date_d, $start_date];
        $weekly_date = [$end_date, $start_date];
        $monthly_date = [$end_date_m, $start_date];
        $yearly_date = [$end_date_y, $start_date];
        
        // dd($daily_date, $weekly_date, $monthly_date, $yearly_date);
        $patient_cases = [];
        $patient_cases = User::withCount(['created_user_cases', 'assign_to_casses', 'planner_casses', 'qa_cases', 'post_processing_cases', 'my_cases', 'sub_client_cases'])->withOut(['permissions', 'roles'])->where('id', auth()->user()->id)->first();
        
        $patient_cases->makeHidden('permissions');
        $patient_cases->makeHidden('roles');
        
        $this->getCasesCounts($patient_cases);
        if(!empty($patient_cases)){
            $this->getWeeklyCasesCount($patient_cases);
            
            // weekly 
            $this->getWeeklyCasesCount($patient_cases, 0);
            $this->getWeeklyCasesCount($patient_cases, 1);
            $this->getWeeklyCasesCount($patient_cases, 2);
            $this->getWeeklyCasesCount($patient_cases, 3);
            $this->getWeeklyCasesCount($patient_cases, 4);
            $this->getWeeklyCasesCount($patient_cases, 5);
            $this->getWeeklyCasesCount($patient_cases, 6);
            $this->getWeeklyCasesCount($patient_cases, 7);
            $this->getWeeklyCasesCount($patient_cases, 8);
            $this->getWeeklyCasesCount($patient_cases, 9);
            $this->getWeeklyCasesCount($patient_cases, 10);
            $this->getWeeklyCasesCount($patient_cases, 11);
            $this->getWeeklyCasesCount($patient_cases, 12);
            $this->getWeeklyCasesCount($patient_cases, 13);
            $this->getWeeklyCasesCount($patient_cases, 14);
            $this->getWeeklyCasesCount($patient_cases, 15);
            $this->getWeeklyCasesCount($patient_cases, 16);

            // // monthly 
            // $this->getMonthlyCasesCount($patient_cases, 0);
            // $this->getMonthlyCasesCount($patient_cases, 1);
            // $this->getMonthlyCasesCount($patient_cases, 2);
            // $this->getMonthlyCasesCount($patient_cases, 3);
            // $this->getMonthlyCasesCount($patient_cases, 4);
            // $this->getMonthlyCasesCount($patient_cases, 5);
            // $this->getMonthlyCasesCount($patient_cases, 6);
            // $this->getMonthlyCasesCount($patient_cases, 7);
            // $this->getMonthlyCasesCount($patient_cases, 8);
            // $this->getMonthlyCasesCount($patient_cases, 9);
            // $this->getMonthlyCasesCount($patient_cases, 10);
            // $this->getMonthlyCasesCount($patient_cases, 11);
            // $this->getMonthlyCasesCount($patient_cases, 12);
            // $this->getMonthlyCasesCount($patient_cases, 13);
            // $this->getMonthlyCasesCount($patient_cases, 14);
            // $this->getMonthlyCasesCount($patient_cases, 15);
            // $this->getMonthlyCasesCount($patient_cases, 16);

        }
        // $this->getDailyCounts($patient_cases, $daily_date, $type = 'daily');
        // $this->getWeeklyCounts($patient_cases, $weekly_date, $type = 'weekly');
        $this->getMonthlyCounts($patient_cases, $monthly_date, $type = 'monthly');
        // $this->getYearlyCounts($patient_cases, $yearly_date, $type = 'yearly');


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

    public function getDailyCounts($patient_cases, $date, $type){

        $data = [];
        $patient_cases['cases_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                    })
                                                    ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                                        if($type == 'daily'){
                                                            return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                                        }else{
                                                            return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                                        }
                                                    })
                                            ->groupBy('date')
                                            ->orderBy('date', 'desc')
                                            ->get();
        $patient_cases['cases_0_count_'.$type] = PatientCase::select(
                                                            DB::raw('DATE(created_at) as date'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 0)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [0])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_1_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 1)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [1])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_2_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 2)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [2])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_3_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 3)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [3])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_4_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 4)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [4])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_5_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 5)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9, 10, 13, 14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_6_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 6)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [6])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_7_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 7)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [7])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_8_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 8)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [8])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_9_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 9)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->count();
        $patient_cases['cases_10_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 10)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [10])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_11_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 11)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [11])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_12_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 12)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [12])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_13_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 13)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [13])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_14_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 14)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_15_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 15)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [15])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();
        $patient_cases['cases_16_count_'.$type] = PatientCase::select(
                                            DB::raw('DATE(created_at) as date'),
                                            DB::raw('count(*) as count')
                                        )->where('status', 16)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [16])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('date')
                                ->orderBy('date', 'desc')
                                ->get();

        return $patient_cases;
    }


    public function getWeeklyCounts($patient_cases, $date, $type){

        $data = [];
        $patient_cases['cases_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())
                                    ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                    })
                                                    ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                                        if($type == 'daily'){
                                                            return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                                        }else{
                                                            return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                                        }
                                                    })
                                            ->orderBy('created_at', 'asc')
                                            ->get();
        $patient_cases['cases_0_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 0)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [0])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_1_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 1)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [1])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_2_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 2)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [2])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_3_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 3)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [3])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_4_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 4)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [4])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_5_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 5)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9, 10, 13, 14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_6_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 6)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [6])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_7_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 7)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [7])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_8_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 8)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [8])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_9_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 9)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->count();
        $patient_cases['cases_10_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 10)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [10])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_11_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 11)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [11])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_12_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 12)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [12])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_13_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 13)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [13])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_14_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 14)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_15_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 15)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [15])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();
        $patient_cases['cases_16_count_'.$type] = PatientCase::where('created_at', '>=', Carbon::now()->subWeek())->where('status', 16)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [16])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();

        return $patient_cases;
    }

    public function getMonthlyCounts($patient_cases, $date, $type){

        $data = [];
        $patient_cases['cases_count_'.$type] = PatientCase::select(
                                                    DB::raw('YEAR(created_at) as year'),
                                                    DB::raw('MONTH(created_at) as month'),
                                                    DB::raw('count(*) as count')
                                                )->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                    })
                                                    ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                                        if($type == 'daily'){
                                                            return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                                        }else{
                                                            return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                                        }
                                                    })
                                            ->groupBy('year', 'month')
                                            ->orderBy('year', 'desc')
                                            ->orderBy('month', 'desc')
                                            ->get();
        $patient_cases['cases_0_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 0)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [0])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_1_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 1)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [1])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_2_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 2)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [2])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_3_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 3)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [3])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_4_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 4)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [4])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_5_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 5)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9, 10, 13, 14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_6_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 6)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [6])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_7_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 7)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [7])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_8_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 8)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [8])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_9_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 9)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->count();
        $patient_cases['cases_10_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 10)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [10])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_11_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 11)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [11])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_12_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 12)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [12])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_13_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 13)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [13])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_14_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 14)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_15_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 15)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [15])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();
        $patient_cases['cases_16_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('MONTH(created_at) as month'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 16)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [16])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->get();

        return $patient_cases;
    }

    public function getYearlyCounts($patient_cases, $date, $type){

        $data = [];
        $patient_cases['cases_count_'.$type] = PatientCase::select(
                                            DB::raw('YEAR(created_at) as year'),
                                            DB::raw('count(*) as count')
                                        )->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                    })
                                                    ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                                        if($type == 'daily'){
                                                            return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                                        }else{
                                                            return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                                        }
                                                    })
                                            ->groupBy('year')
                                            ->orderBy('year', 'desc')
                                            ->get();
        $patient_cases['cases_0_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 0)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [0])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();

        $patient_cases['cases_1_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 1)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [1])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_2_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 2)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [2])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_3_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 3)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [3])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_4_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 4)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [4])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_5_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 5)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9, 10, 13, 14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_6_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 6)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [6])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_7_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 7)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [7])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_8_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 8)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [8])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_9_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 9)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->count();
        $patient_cases['cases_10_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 10)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [10])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_11_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 11)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [11])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_12_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 12)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [12])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_13_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 13)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [13])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_14_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 14)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_15_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 15)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [15])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();
        $patient_cases['cases_16_count_'.$type] = PatientCase::select(
                                                            DB::raw('YEAR(created_at) as year'),
                                                            DB::raw('count(*) as count')
                                                        )->where('status', 16)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [16])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })
                                ->when((!empty($type) && !is_null($type)), function($query) use($date, $type){
                                    if($type == 'daily'){
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }else{
                                        return $query->whereBetween(DB::raw('date(created_at)'), [$date]);
                                    }
                                })
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get();

        return $patient_cases;
    }



    public function getCasesCounts($patient_cases){
        $patient_cases['cases_count'] = PatientCase::when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                    })->count();
        $patient_cases['cases_0_count'] = PatientCase::where('status', 0)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [0])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_1_count'] = PatientCase::where('status', 1)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [1])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_2_count'] = PatientCase::where('status', 2)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [2])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_3_count'] = PatientCase::where('status', 3)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [3])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_4_count'] = PatientCase::where('status', 4)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [4])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_5_count'] = PatientCase::where('status', 5)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9, 10, 13, 14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_6_count'] = PatientCase::where('status', 6)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [6])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_7_count'] = PatientCase::where('status', 7)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [7])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_8_count'] = PatientCase::where('status', 8)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [8])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_9_count'] = PatientCase::where('status', 9)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_10_count'] = PatientCase::where('status', 10)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [10])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_11_count'] = PatientCase::where('status', 11)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [11])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_12_count'] = PatientCase::where('status', 12)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [12])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_13_count'] = PatientCase::where('status', 13)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [13])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_14_count'] = PatientCase::where('status', 14)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_15_count'] = PatientCase::where('status', 15)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [15])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();
        $patient_cases['cases_16_count'] = PatientCase::where('status', 16)
                                                        ->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [16])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where(function($query){
                                            $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                        });
                                    }
                                })->count();

        return $patient_cases;
    }


    public function getWeeklyCasesCount($patient_cases, $count = null){

        if(is_null($count)){
            // $patient_cases['cases_count_weekly']
            $patient_case = PatientCase::where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
                                                        ->when($this->role_name, function($q){
                                                            if($this->role_name == 'post_processing'){
                                                                $q->where('verified_by_client', 1);
                                                            }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                                                $q->where(function($query){
                                                                    $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                                                });
                                                            }
                                                        })->orderBy('created_at', 'asc')->get();
            $grouped_patient_cases = $patient_case->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('l'); // Group by day name
            });
            $daily_patient_cases_counts = [];
            foreach ($grouped_patient_cases as $day => $products) {
                $daily_patient_cases_counts[] = [
                    'day' => $day,
                    'count' => $products->count()
                ];
            }
            $patient_cases['cases_count_weekly'] = $daily_patient_cases_counts;

        }else{

            $patient_case = PatientCase::where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
                                                            ->where('status', $count)
                                                            ->when($this->role_name, function($q){
                                                                if($this->role_name == 'post_processing'){
                                                                    $q->whereIn('status', [0])->where('verified_by_client', 1);
                                                                }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                                                    $q->where(function($query){
                                                                        $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                                                    });
                                                                }
                                                            })->orderBy('created_at', 'asc')->get();
            
            $grouped_patient_cases = $patient_case->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('l'); // Group by day name
            });
            $daily_patient_cases_counts = [];
            foreach ($grouped_patient_cases as $day => $products) {
                $daily_patient_cases_counts[] = [
                    'day' => $day,
                    'count' => $products->count()
                ];
            }
            $patient_cases['cases_'.$count.'_count_weekly'] = $daily_patient_cases_counts;
        }
        return $patient_cases;

    }

    public function getMonthlyCasesCount($patient_cases, $count = null){

        if(is_null($count)){
            $patient_case = PatientCase::where('created_at', '>=', Carbon::now()->subYear())
                                                        ->when($this->role_name, function($q){
                                                            if($this->role_name == 'post_processing'){
                                                                $q->where('verified_by_client', 1);
                                                            }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                                                $q->where(function($query){
                                                                    $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                                                });
                                                            }
                                                        })->orderBy('created_at', 'asc')->get();
            $grouped_patient_cases = $patient_case->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('F'); // Group by month name
            });
            $daily_patient_cases_counts = [];
            foreach ($grouped_patient_cases as $month => $products) {
                $daily_patient_cases_counts[] = [
                    'month' => $month,
                    'count' => $products->count()
                ];
            }
            $patient_cases['cases_count_monthly'] = $daily_patient_cases_counts;

        }else{

            $patient_case = PatientCase::where('created_at', '>=', Carbon::now()->subYear())
                                                            ->where('status', $count)
                                                            ->when($this->role_name, function($q){
                                                                if($this->role_name == 'post_processing'){
                                                                    $q->whereIn('status', [0])->where('verified_by_client', 1);
                                                                }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                                                    $q->where(function($query){
                                                                        $query->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                                                    });
                                                                }
                                                            })->orderBy('created_at', 'asc')->get();
            
            $grouped_patient_cases = $patient_case->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('F'); // Group by month name
            });
            $daily_patient_cases_counts = [];
            foreach ($grouped_patient_cases as $month => $products) {
                $daily_patient_cases_counts[] = [
                    'month' => $month,
                    'count' => $products->count()
                ];
            }
            $patient_cases['cases_'.$count.'_count_monthly'] = $daily_patient_cases_counts;
        }
        return $patient_cases;

    }
}
