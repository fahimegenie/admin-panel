<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ImageStorageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends Controller
{

    use ImageStorageTrait;
    
    /**
     * @var array
     */
    protected $response = [];
    protected $status = 200;

    public function uploadFile(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:2048000', // max file size in KB
        ]);
  
        if($validator->fails()){
            $this->status = 422;
            $this->response['status'] = $this->status;
            $this->response['success'] = false;
            $this->response['message'] = $validator->messages()->first();
            return response()->json($this->response, $this->status);
        }
        

        if ($request->file('file')->isValid()) {

            // $folder = 'uploads/stl'; 
            // $path = $this->storeImage($request->file('file'), $folder);


            $startTime = microtime(true);
            // Validate and store the file
            $request->validate([
                'file' => 'required|file', // 10 MB max
            ]);

            $file = $request->file('file');
            $file->store('uploads');

            $endTime = microtime(true);
            $uploadTime = $endTime - $startTime;

            // Get the file size in megabytes
            $fileSize = $file->getSize() / 1024 / 1024;

            // Calculate upload speed in Mbps
            $uploadSpeed = ($fileSize / $uploadTime) * 8;

            $data = [
                'uploadTime' => $uploadTime,
                'uploadSpeed' => $uploadSpeed,
                'fileSize' => $fileSize,
            ];
            // $path = $request->file('file')->store('uploads', 'public');

            $this->response['message'] = 'File upload successfully!';
            $this->response['data'] = $data;
            $this->response['status'] = $this->status;
            return response()->json($this->response, $this->status);
                    }

        $this->response['message'] = 'Failed to upload file!';
        $this->response['data'] = [];
        $this->response['status'] = $this->status;
        return response()->json($this->response, $this->status);
        
    }

}
