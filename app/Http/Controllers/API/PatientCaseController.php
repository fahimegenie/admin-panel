<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CasesStatusUser;
use App\Models\CasesStatusUsersComment;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Traits\ImageStorageTrait;
use App\Models\PatientCase;
use App\Models\Xray;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Repositories\ActivityLogs;

class PatientCaseController extends Controller
{
    
    use ImageStorageTrait;

    private $activityLog;
    
     /**
     * @var array
     */
    protected $response = [];
    protected $status = 200;
    protected $user_id = 0;
    protected $role_name = '';
    public function __construct(ActivityLogs $activityLog=null){
        if(!empty(auth()->user())){
            $this->user_id = auth()->user()->id;
            $this->role_name = auth()->user()->role_name;
        }

        $this->activityLog = $activityLog;
    }
    
    public function index(){
        $patient_cases = [];
            $patient_cases = PatientCase::with(['users','images', 'xrays', 'created_user', 'case_plans', 'case_status_users', 'case_status_users.cases_status_users_comments', 'planner', 'qa', 'post_processing'])->when($this->role_name, function($q){
                                    if($this->role_name == 'post_processing'){
                                        $q->whereIn('status', [9, 10, 13, 14])->where('verified_by_client', 1);
                                    }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id)->Orwhere('client_id', auth()->user()->id);
                                    }
                                })->orderBy('is_priority', 'DESC')
                                ->get();
        
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

    /**
     * Register a PatientCase.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'extraction' => 'required',
            'attachments' => 'required',
            'case_id' => 'required|unique:patient_cases',
            'age' => 'required',
            'gender' => 'required',
            'ipr' => 'required',
            'chief_complaint' => 'required',
            'treatment_plan' => 'required',
            'stl_upper_file' => 'required',
            'stl_lower_file' => 'required',
            'stl_byte_scan_file' => 'required',
            'is_priority' => 'nullable|numeric',
        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }
        
        $patient_cases = new PatientCase();
        $patient_cases->name = $request->name;
        $patient_cases->email = $request->email;
        $patient_cases->extraction = $request->extraction;
        $patient_cases->attachments = $request->attachments;
        $patient_cases->case_id = $request->case_id;
        $patient_cases->age = $request->age;
        $patient_cases->gender = $request->gender;
        $patient_cases->chief_complaint = $request->chief_complaint;
        $patient_cases->treatment_plan = $request->treatment_plan;
        $patient_cases->created_by = auth()->user()->id;
        $patient_cases->verified_by_client = 1;
        if(auth()->user() && !empty(auth()->user()->client_id)){
            $patient_cases->sub_client_id = auth()->user()->id;
            $patient_cases->client_id = auth()->user()->client_id;
            $patient_cases->verified_by_client = 0;
        }
        if(isset($request->is_priority) && !empty($request->is_priority)){
            $patient_cases->is_priority = $request->is_priority;
        }

        if(isset($request->created_by_admin) && !empty($request->created_by_admin)){
            $patient_cases->created_by_admin = $request->created_by_admin;
        }
        if(isset($request->expected_time) && !empty($request->expected_time)){
            $patient_cases->expected_time = $request->expected_time;
        }
        if(isset($request->tooth_label_format) && !empty($request->tooth_label_format)){
            $patient_cases->tooth_label_format = $request->tooth_label_format;
        }
        if(isset($request->setup_type) && !empty($request->setup_type)){
            $patient_cases->setup_type = $request->setup_type;
        }
        
        $stl_upper_file = '';
        $stl_lower_file = '';
        $stl_byte_scan_file = '';
        if($request->hasFile('stl_upper_file')){
            $picture = $request->file('stl_upper_file');
            $folder = 'uploads/stl'; 
            $stl_upper_file = $this->storeImage($picture, $folder);
        }
        if($request->hasFile('stl_lower_file')){
            $picture = $request->file('stl_lower_file');
            $folder = 'uploads/stl'; 
            $stl_lower_file = $this->storeImage($picture, $folder);
        }
        if($request->hasFile('stl_byte_scan_file')){
            $picture = $request->file('stl_byte_scan_file');
            $folder = 'uploads/stl'; 
            $stl_byte_scan_file = $this->storeImage($picture, $folder);
        }
        // if($request->hasFile('ipr')){
        //     $picture = $request->file('ipr');
        //     $folder = 'uploads/pdf'; 
        //     $ipr = $this->storeImage($picture, $folder);
        // }
        $patient_cases->stl_upper_file = $stl_upper_file;
        $patient_cases->stl_lower_file = $stl_lower_file;
        $patient_cases->stl_byte_scan_file = $stl_byte_scan_file;
        $patient_cases->ipr = $request->ipr;
        $patient_cases->save();

        $p_case_id = $patient_cases->id;
        // save patient images files
        if(isset($request->image_files) && !empty($request->image_files)){
            foreach ($request->image_files as $key => $value) {
                if(is_file($value)){
                    $images = new Image();
                    $picture = $value;
                    $folder = 'uploads/images'; 
                    $file_name = $this->storeImage($picture, $folder);
                    $images->file_name = $file_name;
                    $images->type = 'patient_cases';
                    $images->p_case_id = $p_case_id;
                    $images->save();
                }
            }
        }
        // save the patient xrays files 
        if(isset($request->xrays_files) && !empty($request->xrays_files)){
            foreach ($request->xrays_files as $key => $value) {
                if(is_file($value)){
                    $xrays = new Xray();
                    $picture = $value;
                    $folder = 'uploads/xrays'; 
                    $file_name = $this->storeImage($picture, $folder);
                    $xrays->file_name = $file_name;
                    $xrays->type = 'patient_cases';
                    $xrays->p_case_id = $p_case_id;
                    $xrays->save();
                }
            }
        }
        
        $this->response['message'] = 'Patient case created successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }

    public function detail($guid){

        $patient_cases = PatientCase::with(['users','images', 'xrays', 'created_user', 'case_plans', 'case_status_users', 'case_status_users.cases_status_users_comments', 'planner', 'qa', 'post_processing'])
                                // ->when($this->role_name, function($q){
                                //     if($this->role_name == 'post_processing'){
                                //         $q->whereIn('status', [8, 9, 10]);
                                //     }else if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                //         $q->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id);
                                //     }
                                // })
                                ->where('guid', $guid)->first();

        
        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $this->response['message'] = 'Patient case detail!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
    public function update(Request $request, $guid){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'extraction' => 'required',
            'attachments' => 'required',
            'case_id' => 'required|unique:patient_cases,id',
            'age' => 'required',
            'gender' => 'required',
            'ipr' => 'required',
            'chief_complaint' => 'required',
            'treatment_plan' => 'required',
            'is_priority' => 'nullable|numeric',
        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status); 
        }
        
        $patient_cases = PatientCase::where('created_by', $this->user_id)->where('client_id', $this->user_id)->where('guid', $guid)->first();
        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);     
        }
        
        $patient_cases->name = $request->name;
        $patient_cases->email = $request->email;
        $patient_cases->extraction = $request->extraction;
        $patient_cases->attachments = $request->attachments;
        $patient_cases->case_id = $request->case_id;
        $patient_cases->age = $request->age;
        $patient_cases->gender = $request->gender;
        $patient_cases->ipr = $request->ipr;
        $patient_cases->chief_complaint = $request->chief_complaint;
        $patient_cases->treatment_plan = $request->treatment_plan;
        
        $stl_upper_file = $patient_cases->stl_upper_file;
        $stl_lower_file = $patient_cases->stl_lower_file;
        $stl_byte_scan_file = $patient_cases->stl_byte_scan_file;
        // $ipr = $patient_cases->ipr;
        if(isset($request->is_priority) && !empty($request->is_priority)){
            $patient_cases->is_priority = $request->is_priority;
        }

        if(isset($request->expected_time) && !empty($request->expected_time)){
            $patient_cases->expected_time = $request->expected_time;
        }
        if(isset($request->tooth_label_format) && !empty($request->tooth_label_format)){
            $patient_cases->tooth_label_format = $request->tooth_label_format;
        }
        if(isset($request->setup_type) && !empty($request->setup_type)){
            $patient_cases->setup_type = $request->setup_type;
        }

        $patient_cases->case_version = ((int)$patient_cases->case_version + 1);

        if($request->hasFile('stl_upper_file')){
            $picture = $request->file('stl_upper_file');
            $folder = 'uploads/stl'; 
            $stl_upper_file = $this->storeImage($picture, $folder);
        }
        if($request->hasFile('stl_lower_file')){
            $picture = $request->file('stl_lower_file');
            $folder = 'uploads/stl'; 
            $stl_lower_file = $this->storeImage($picture, $folder);
        }
        if($request->hasFile('stl_byte_scan_file')){
            $picture = $request->file('stl_byte_scan_file');
            $folder = 'uploads/stl'; 
            $stl_byte_scan_file = $this->storeImage($picture, $folder);
        }
        // if($request->hasFile('ipr')){
        //     $picture = $request->file('ipr');
        //     $folder = 'uploads/pdf'; 
        //     $ipr = $this->storeImage($picture, $folder);
        // }
        $patient_cases->stl_upper_file = $stl_upper_file;
        $patient_cases->stl_lower_file = $stl_lower_file;
        $patient_cases->stl_byte_scan_file = $stl_byte_scan_file;
        // $patient_cases->ipr = $ipr;
        $patient_cases->save();

        $p_case_id = $patient_cases->id;
        // save patient images files
        if(isset($request->image_files) && !empty($request->image_files)){
            foreach ($request->image_files as $key => $value) {
                if(is_file($value)){
                    $images = new Image();
                    $picture = $value;
                    $folder = 'uploads/images'; 
                    $file_name = $this->storeImage($picture, $folder);
                    $images->file_name = $file_name;
                    $images->type = 'patient_cases';
                    $images->p_case_id = $p_case_id;
                    $images->save();
                }
            }
        }
        // save the patient xrays files 
        if(isset($request->xrays_files) && !empty($request->xrays_files)){
            foreach ($request->xrays_files as $key => $value) {
                if(is_file($value)){
                    $xrays = new Xray();
                    $picture = $value;
                    $folder = 'uploads/xrays'; 
                    $file_name = $this->storeImage($picture, $folder);
                    $xrays->file_name = $file_name;
                    $xrays->type = 'patient_cases';
                    $xrays->p_case_id = $p_case_id;
                    $xrays->save();
                }
            }
        }


        // $changeFields = $patient_cases->getChanges();
        // if (!empty($changeFields)) {
        //     foreach ($changeFields as $key => $changeField){
        //         if(!empty($changeField) && $key != 'created_at' && $key != 'updated_at'){
        //             $this->activityLog->addLog(auth()->user(), ucfirst($this->role_name), "updated", $key.' => '.$changeField, $changeFields->id, '', 'patient_cases');
        //         }
        //     }
        // }

    
        $this->response['message'] = 'Patient case updated successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    public function destroy($guid){
        $patient_cases = PatientCase::where('guid', $guid)->first();
        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        PatientCase::where('id', $patient_cases->id)->delete();

        $this->response['message'] = 'Patient case deleted successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }

    public function case_assign_to(Request $request){

        $validator = Validator::make($request->all(), [
            'p_case_id' => 'required|numeric',
            'user_id' => 'required|numeric',

        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status); 
        }
        $user = User::findOrFail($request->user_id);
        
        $patient_cases = PatientCase::findOrFail($request->p_case_id);
        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);     
        }
        $patient_cases->assign_to = $request->user_id;
        $patient_cases->status = 2;

        if(!empty($user) && !empty($user->roles) && !empty($user->roles[0]) && $user->roles[0]['name'] && $user->roles[0]['name'] == 'quality_check'){
            $patient_cases->qa_id = $user->id;
        }
        if(!empty($user) && !empty($user->roles) && !empty($user->roles[0]) && $user->roles[0]['name'] && $user->roles[0]['name'] == 'treatment_planner'){
            $patient_cases->planner_id = $user->id;
        }
        if(!empty(auth()->user()->roles) && !empty(auth()->user()->roles) && !empty(auth()->user()->roles[0]) && auth()->user()->roles[0]['name'] && auth()->user()->roles[0]['name'] == 'treatment_planner'){
            $patient_cases->qa_id = auth()->user()->id;
        }
        $patient_cases->save();

        $this->response['message'] = 'Patient case assigned successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }

    public function rejected_by_quality_check(Request $request){

        $validator = Validator::make($request->all(), [
            'p_case_id' => 'required|numeric',
            'user_id' => 'required|numeric',

        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status); 
        }
        $user = User::findOrFail($request->user_id);
        
        $patient_cases = PatientCase::findOrFail($request->p_case_id);
        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);     
        }
        $patient_cases->assign_to = $request->user_id;
        $patient_cases->status = 2;

        if(!empty($user) && !empty($user->roles) && !empty($user->roles[0]) && $user->roles[0]['name'] && $user->roles[0]['name'] == 'treatment_planner'){
            $patient_cases->planner_id = $user->id;
        }
        if(!empty(auth()->user()->roles) && !empty(auth()->user()->roles) && !empty(auth()->user()->roles[0]) && auth()->user()->roles[0]['name'] && auth()->user()->roles[0]['name'] == 'quality_check'){
            $patient_cases->qa_id = auth()->user()->id;
        }
        $patient_cases->save();

        $this->response['message'] = 'Patient case assigned successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
        
    }

    // 
    public function update_patient_case_status(Request $request){

        $validator = Validator::make($request->all(), [
            'p_case_id' => 'required|numeric',
            'user_id' => 'required|numeric',
            'case_status' => 'required|numeric'

        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status); 
        }
        $user = User::findOrFail($request->user_id);
        
        
        $patient_cases = PatientCase::findOrFail($request->p_case_id);
        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);     
        }
        $patient_cases->assign_to = $request->user_id;
        $patient_cases->status = $request->case_status;

        if(!empty($user) && !empty($user->roles) && !empty($user->roles[0]) && $user->roles[0]['name'] && $user->roles[0]['name'] == 'treatment_planner'){
            $patient_cases->planner_id = $user->id;
        }
        if(!empty(auth()->user()->roles) && !empty(auth()->user()->roles) && !empty(auth()->user()->roles[0]) && auth()->user()->roles[0]['name'] && auth()->user()->roles[0]['name'] == 'quality_check'){
            $patient_cases->qa_id = auth()->user()->id;
        }

        if(!empty(auth()->user()->roles) && !empty(auth()->user()->roles) && !empty(auth()->user()->roles[0]) && auth()->user()->roles[0]['name'] && auth()->user()->roles[0]['name'] == 'post_processing'){
            $patient_cases->post_processing_id = auth()->user()->id;
        }

        if(isset($request->stl_file_by_post_processing) && !empty($request->stl_file_by_post_processing)){
            if($request->hasFile('stl_file_by_post_processing')){
                $file_name = '';
                $picture = $request->file('stl_file_by_post_processing');
                $folder = 'uploads/stl'; 
                $file_name = $this->storeImage($picture, $folder);
                $patient_cases->stl_file_by_post_processing = $file_name;

                $patient_cases->scan_version = ($patient_cases->scan_version + 1);
            }

        }
        if(isset($request->container_file_by_post_processing) && !empty($request->container_file_by_post_processing)){
            if($request->hasFile('container_file_by_post_processing')){
                $file_name = '';
                $picture = $request->file('container_file_by_post_processing');
                $folder = 'uploads/stl'; 
                $file_name = $this->storeImage($picture, $folder);
                $patient_cases->container_file_by_post_processing = $file_name;

                $patient_cases->scan_version = ($patient_cases->scan_version + 1);
            }

        }
        
        
        $patient_cases->save();


        $cases_status_user = CasesStatusUser::where('p_case_id', $request->p_case_id)->where('user_id', $request->user_id)->where('case_status', $request->case_status)->first();
        if(!$cases_status_user){
            $cases_status_user = new CasesStatusUser();
        }
        $cases_status_user->p_case_id = $request->p_case_id;
        $cases_status_user->user_id = $request->user_id;
        $cases_status_user->case_status = $request->case_status;
        $cases_status_user->save();
        
        $cases_status_user_id = $cases_status_user->id;

        if(isset($request->comments) && !empty($request->comments)){
            $cases_status_users_comment = new CasesStatusUsersComment();
            $cases_status_users_comment->pcsu_id = $cases_status_user_id;
            $cases_status_users_comment->comments = $request->comments;
            $cases_status_users_comment->case_status = $request->case_status;
            $cases_status_users_comment->save();
        }

        $this->response['message'] = 'Patient case assigned successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }

    // 
    public function getCasesByStatus($status=1){
        $patient_cases = [];
            $patient_cases = PatientCase::with(['users','images', 'xrays', 'created_user', 'case_plans', 'case_status_users', 'case_status_users.cases_status_users_comments'])->when($this->role_name, function($q){
                                    if($this->role_name != 'super_admin' && $this->role_name != 'case_submission'){
                                        $q->where('created_by', auth()->user()->id)->orWhere('assign_to', auth()->user()->id);
                                    }
                                })->where('status', $status)->paginate('10');
        
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

    public function verified_by_client($guid){
        $patient_cases = PatientCase::where('guid', $guid)->first();

        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }

        $patient_cases->verified_by_client = 1;
        $patient_cases->save();

        $this->response['message'] = 'Verified by client successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
}
