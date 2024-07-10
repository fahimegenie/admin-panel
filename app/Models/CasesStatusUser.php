<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class CasesStatusUser extends Model
{
    use HasFactory, Uuids;

    protected $table = 'cases_status_users';
    protected $primaryKey = 'id';

    protected $fillable = [
                    'guid',
                    'p_case_id',
                    'user_id',
                    'case_status',
                    'status',
        ];

    
    public function cases_status_users_comments(){
        return $this->hasMany(CasesStatusUsersComment::class, 'pcsu_id', 'id')->orderBy('id', 'DESC');
    }
}
