<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Traits\ImageStorageTrait;
use App\Models\PatientCase;
use App\Models\Xray;
use Illuminate\Support\Facades\Validator;

class PatienCaseController extends Controller
{
    
    use ImageStorageTrait;
    
     /**
     * @var array
     */
    protected $response = [];
    protected $status = 200;
    
    public function index(){
        $patient_cases = PatientCase::paginate('10');
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
            'stl_byte_scan_file' => 'required'
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
        $patient_cases->ipr = $request->ipr;
        $patient_cases->chief_complaint = $request->chief_complaint;
        $patient_cases->treatment_plan = $request->treatment_plan;
        $patient_cases->created_by = auth()->user()->id;
        
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
        $patient_cases->stl_upper_file = $stl_upper_file;
        $patient_cases->stl_lower_file = $stl_lower_file;
        $patient_cases->stl_byte_scan_file = $stl_byte_scan_file;
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

    public function detail($p_case_id){

        $patient_cases = PatientCase::find($p_case_id);
        
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
    public function update(Request $request, $p_case_id){

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
        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status); 
        }
        
        $patient_cases = PatientCase::find($p_case_id);
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
        $patient_cases->created_by = auth()->user()->id;
        
        $stl_upper_file = $patient_cases->stl_upper_file;
        $stl_lower_file = $patient_cases->stl_lower_file;
        $stl_byte_scan_file = $patient_cases->stl_byte_scan_file;

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
        $patient_cases->stl_upper_file = $stl_upper_file;
        $patient_cases->stl_lower_file = $stl_lower_file;
        $patient_cases->stl_byte_scan_file = $stl_byte_scan_file;
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

    
        $this->response['message'] = 'Patient case updated successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    public function destroy($p_case_id){
        $patient_cases = PatientCase::find($p_case_id);
        if(empty($patient_cases)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $patient_cases->delete();

        $this->response['message'] = 'Patient case deleted successfully!';
        $this->response['data'] = $patient_cases;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
}
