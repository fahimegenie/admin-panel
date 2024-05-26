<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Team extends Model
{
    use HasFactory, Uuids;

    protected $table = 'users_teams';
    protected $primaryKey = 'id';

    protected $fillable = [
            'guid',
            'name',
            'logo',
            'created_by',
            'status',
        ];
}
