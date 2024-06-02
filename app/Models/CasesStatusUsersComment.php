<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;


class CasesStatusUsersComment extends Model
{
    use HasFactory, Uuids;

    protected $table = 'cases_status_users_comments';
    protected $primaryKey = 'id';

    protected $fillable = [
            'guid',
            'pcsu_id',
            'comments',
            'case_status',
            'status',
        ];
}
