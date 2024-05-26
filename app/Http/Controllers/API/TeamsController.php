<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ImageStorageTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\Team;


class TeamsController extends Controller
{
    
    use ImageStorageTrait;
    
     /**
     * @var array
     */
    protected $response = [];
    protected $status = 200;
    
    public function index(){
        $teams = Team::paginate('10');
        if(empty($teams)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $this->response['message'] = 'Teams list!';
        $this->response['data'] = $teams;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    /**
     * Register a teams.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:teams',
        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }
        
        $teams = new Team();
        $teams->name = $request->name;
        $teams->created_by = auth()->user()->id;
        $logo = '';
        if($request->hasFile('logo')){
            $picture = $request->file('logo');
            $folder = 'uploads/images'; 
            $logo = $this->storeImage($picture, $folder);
        }
        $teams->logo = $logo;
        $teams->save();

        $this->response['message'] = 'Team created successfully!';
        $this->response['data'] = $teams;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }

    public function detail($guid){

        $teams = Team::where('guid', $guid)->first();
        
        if(empty($teams)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $this->response['message'] = 'Team detail!';
        $this->response['data'] = $teams;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }
    public function update(Request $request, $guid){

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:teams,id',
        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status); 
        }
        
        $teams = Team::find($guid);
        if(empty($teams)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);     
        }
        
        $teams->name = $request->name;
        $teams->created_by = auth()->user()->id;
        $logo = '';
        if($request->hasFile('logo')){
            $picture = $request->file('logo');
            $folder = 'uploads/images'; 
            $logo = $this->storeImage($picture, $folder);
        }
        $teams->logo = $logo;
        $teams->save();

    
        $this->response['message'] = 'Team updated successfully!';
        $this->response['data'] = $teams;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
    }

    public function destroy($guid){
        $teams = Team::find($guid);
        if(empty($teams)){
            $this->status = 400;
            $this->response['status'] = $this->status;
            $this->response['success'] = true;
            $this->response['message'] = 'Record not found';
            return response()->json($this->response, $this->status);      
        }
        $teams->delete();

        $this->response['message'] = 'Team deleted successfully!';
        $this->response['data'] = $teams;
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);

    }

}
