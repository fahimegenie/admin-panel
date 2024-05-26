<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $roles = [
                'super_admin',
                'client',
                'sub_client',
                'case_submission',
                'quality_check',
                'post_processing',
                'treatment_planner',
            ];    
        if(!empty($roles)){
            foreach ($roles as $key => $value) {
                $role = new Role();
                $role->name = $value;
                $role->save();
            }
        }

        $permissions = [
                'users-list',
                'users-store',
                'users-detail',
                'users-update',
                'users-delete',
                'roles-list',
                'roles-store',
                'roles-detail',
                'roles-update',
                'roles-delete',
                'permissions-list',
                'permissions-store',
                'permissions-detail',
                'permissions-update',
                'permissions-delete',
                'patient-cases-list',
                'patient-cases-store',
                'patient-cases-detail',
                'patient-cases-update',
                'patient-cases-delete',
                'patient-cases-case-assign-to',
                'pending-approvals-list',
                'pending-approvals-store',
                'pending-approvals-detail',
                'pending-approvals-update',
                'pending-approvals-delete',
                'modification-receiveds-list',
                'modification-receiveds-store',
                'modification-receiveds-detail',
                'modification-receiveds-update',
                'modification-receiveds-delete',
                'need-more-info-list',
                'need-more-info-store',
                'need-more-info-detail',
                'need-more-info-update',
                'need-more-info-delete',
                'step-file-ready-list',
                'step-file-ready-store',
                'step-file-ready-detail',
                'step-file-ready-update',
                'step-file-ready-delete',
                'teams-list',
                'teams-store',
                'teams-detail',
                'teams-update',
                'teams-delete',
            ];

        if(!empty($permissions)){
            foreach ($permissions as $key => $value) {
                $permission = new Permission();
                $permission->name = $value;
                $permission->save();
            }
        }

        $allPermissions = Permission::all();
        $adminRole = Role::where('name', 'super_admin')->first();

        // create super admin 
        $user = new User();
        $user->email = 'admin@admin.com';
        $user->password = bcrypt(1111);
        $user->first_name = 'super admin';
        $user->last_name = 'super admin';
        $user->username = 'super admin';
        $user->mobile_number = '3412341234';
        $user->profile_pic = 'no-image.png';
        $user->save();

        if ($user) {
            $user->assignRole($adminRole);
        }

        $role = Role::where('name', 'client')->first();
        // create client user
        $user = new User();
        $user->email = 'client@client.com';
        $user->password = bcrypt(1111);
        $user->first_name = 'client';
        $user->last_name = 'client';
        $user->username = 'client';
        $user->mobile_number = '3412341234';
        $user->profile_pic = 'no-image.png';
        $user->save();
        if ($user) {
            $user->assignRole($role);
        }
        $user_id = $user->id;
        // create sub client user
        $user = new User();
        $user->email = 'subclient@subclient.com';
        $user->password = bcrypt(1111);
        $user->first_name = 'subclient';
        $user->last_name = 'subclient';
        $user->username = 'subclient';
        $user->mobile_number = '3412341234';
        $user->profile_pic = 'no-image.png';
        $user->client_id = $user_id;
        $user->save();
        $role = Role::where('name', 'sub_client')->first();
        if ($user) {
            $user->assignRole($role);
        }
        // create a case submission user
        $user = new User();
        $user->email = 'casesubmission@casesubmission.com';
        $user->password = bcrypt(1111);
        $user->first_name = 'casesubmission';
        $user->last_name = 'casesubmission';
        $user->username = 'casesubmission';
        $user->mobile_number = '3412341234';
        $user->profile_pic = 'no-image.png';
        $user->save();
        $role = Role::where('name', 'case_submission')->first();
        if ($user) {
            $user->assignRole($role);
        }
        // create a quality check user
        $user = new User();
        $user->email = 'qualitycheck@qualitycheck.com';
        $user->password = bcrypt(1111);
        $user->first_name = 'qualitycheck';
        $user->last_name = 'qualitycheck';
        $user->username = 'qualitycheck';
        $user->mobile_number = '3412341234';
        $user->profile_pic = 'no-image.png';
        $user->save();
        $role = Role::where('name', 'quality_check')->first();
        if ($user) {
            $user->assignRole($role);
        }
        // create a post processing user
        $user = new User();
        $user->email = 'postprocessing@postprocessing.com';
        $user->password = bcrypt(1111);
        $user->first_name = 'postprocessing';
        $user->last_name = 'postprocessing';
        $user->username = 'postprocessing';
        $user->mobile_number = '3412341234';
        $user->profile_pic = 'no-image.png';
        $user->save();
        $role = Role::where('name', 'post_processing')->first();
        if ($user) {
            $user->assignRole($role);
        }
        // create a treatment planner user
        $user = new User();
        $user->email = 'treatmentplanner@treatmentplanner.com';
        $user->password = bcrypt(1111);
        $user->first_name = 'treatmentplanner';
        $user->last_name = 'treatmentplanner';
        $user->username = 'treatmentplanner';
        $user->mobile_number = '3412341234';
        $user->profile_pic = 'no-image.png';
        $user->save();
        $role = Role::where('name', 'treatment_planner')->first();
        if ($user) {
            $user->assignRole($role);
        }

    }
}
