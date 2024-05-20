<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class ModificationReceived extends Model
{
    use HasFactory, Uuids;

    protected $table = 'modification_receiveds';
    protected $primaryKey = 'id';

    protected $fillable = [
                    'guid',
                    'p_case_id',
                    'simulation_link_url',
                    'ipr_chart',
                    'comments',
                    'created_by',
                    'status',
        ];

    protected $with = ['patinet_cases'];

    public function patinet_cases(){
        return $this->belongsTo(PatientCase::class, 'p_case_id');
    }
}
