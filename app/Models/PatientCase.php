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
                'status'
        ];

    protected $with = ['images', 'xrays'];

    public function images(){
        return $this->hasMany(Image::class, 'p_case_id');
    }
    public function xrays(){
        return $this->hasMany(Xray::class, 'p_case_id');
    }
    
}