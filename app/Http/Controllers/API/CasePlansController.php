<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ImageStorageTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\CasePlan;

class CasePlansController extends Controller
{
     use ImageStorageTrait;
    
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
    
    public function index(){
        $case_plans = [];
            $case_plans = CasePlan::when($this->role_name, function($q){
                                    if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where('created_by', auth()->user()->id);
                                    }
                                })->paginate('10');
        
        if(empty($case_plans)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $this->response['message'] = 'Case plans list!';
        $this->response['data'] = $case_plans;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    /**
     * Register a PatientCase.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'p_case_id' => 'required',
            'case_plans' => 'required'
        ]);

        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }


        $case_plans = [];
        if(isset($request->case_plans) && !empty($request->case_plans)){
            $case_plans = json_decode($request->case_plans, true);
        }
        if(!empty($case_plans)){
            foreach ($case_plans as $key => $value) {
                $case_plans = new CasePlan();
                $case_plans->p_case_id = $request->p_case_id;
                $case_plans->text_notes = $value['text_notes'];
                $case_plans->simulation_link_url = $value['simulation_link_url'];
                $case_plans->created_by = auth()->user()->id;
                $ipr = '';
                if(is_file($value['ipr_chart'])){
                    $picture = $value->file('ipr_chart');
                    $folder = 'uploads/pdf'; 
                    $ipr = $this->storeImage($picture, $folder);
                }
                $case_plans->ipr_chart = $ipr;
                if(isset($value->status)){
                    $case_plans->status = $value->status;
                }
                $case_plans->save();
            }
        }
        

        
        $this->response['message'] = 'Case plan created successfully!';
        $this->response['data'] = $case_plans;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }

    public function detail($guid){

        $case_plans = CasePlan::where('created_by', $this->user_id)->where('guid', $guid)->first();
        
        if(empty($case_plans)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $this->response['message'] = 'Case plan detail!';
        $this->response['data'] = $case_plans;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
    public function update(Request $request, $guid=0){

        $validator = Validator::make($request->all(), [
            'p_case_id' => 'required',
            'case_plans' => 'required'
        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status); 
        }


        CasePlan::where('p_case_id')->delete();
        
        $case_plans = [];
        if(isset($request->case_plans) && !empty($request->case_plans)){
            $case_plans = json_decode($request->case_plans, true);
        }
        if(!empty($case_plans)){
            foreach ($case_plans as $key => $value) {
                $case_plans = new CasePlan();
                $case_plans->p_case_id = $request->p_case_id;
                $case_plans->text_notes = $value['text_notes'];
                $case_plans->simulation_link_url = $value['simulation_link_url'];
                $case_plans->created_by = auth()->user()->id;
                $ipr = '';
                if(is_file($value['ipr_chart'])){
                    $picture = $value->file('ipr_chart');
                    $folder = 'uploads/pdf'; 
                    $ipr = $this->storeImage($picture, $folder);
                }
                $case_plans->ipr_chart = $ipr;
                if(isset($value->status)){
                    $case_plans->status = $value->status;
                }
                $case_plans->save();
            }
        }


    
        $this->response['message'] = 'Case plan updated successfully!';
        $this->response['data'] = $case_plans;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    public function destroy($guid){
        $case_plans = CasePlan::where('created_by', $this->user_id)->where('guid', $guid)->first();
        if(empty($case_plans)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        CasePlan::where('id', $case_plans->id)->delete();

        $this->response['message'] = 'Case plan deleted successfully!';
        $this->response['data'] = $case_plans;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }

}