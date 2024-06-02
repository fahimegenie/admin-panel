<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class PatientCase extends Model
{
    use HasFactory, Uuids;


    protected $table = 'patient_cases';
    protected $primaryKey = 'id';

    protected $fillable = [
                'guid',
                'name',
                'email',
                'extraction',
                'attachments',
                'case_id',
                'age',
                'gender',
                'ipr',
                'chief_complaint',
                'treatment_plan',
                'stl_upper_file',
                'stl_lower_file',
                'stl_byte_scan_file',
                'created_by',
                'status',
                'assign_to',
                'created_by_admin',
                'planner_id',
                'qa_id'
        ];



    public function images(){
        return $this->hasMany(Image::class, 'p_case_id');
    }
    public function xrays(){
        return $this->hasMany(Xray::class, 'p_case_id');
    }
    public function created_user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    
    public function case_plans(){
        return $this->belongsTo(CasePlan::class, 'id', 'p_case_id');
    }
    public function users(){
        return $this->belongsTo(User::class, 'assign_to', 'id');
    }

    public function case_status_users(){
        return $this->belongsTo(CasesStatusUser::class, 'id', 'p_case_id');
    }

}


// 0 => 'pending', 1 => 'treatment_planning', 2 => 'quality_checking', 3 => 'treatment_planning_upload', 4 => 'pending_step_files', 5 => 'step_files_uploaded'
