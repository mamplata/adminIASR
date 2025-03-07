<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Program;

class DepartmentProgramSeeder extends Seeder
{
    public function run()
    {
        // Define your departments and programs as provided
        $departments = ['CCS', 'CAS', 'CHAS', 'COE', 'CBAA', 'COED'];
        $departmentPrograms = [
            'CCS'  => ['BSCS', 'BSIT'],
            'CAS'  => ['BSPSY'],
            'CHAS' => ['BSN'],
            'COE'  => ['BSCPE', 'BSECE', 'BSIE'],
            'CBAA' => ['BSA', 'BSBDA-MM', 'BSBDA-FM'],
            'COED' => ['BEED', 'BSEDE', 'BSEDM', 'BSEDF', 'BSEDSS']
        ];

        // Loop through each department, create the department record,
        // then create each related program record.
        foreach ($departments as $deptCode) {
            // Check if the department already exists
            $department = Department::firstOrCreate(['code' => $deptCode]);

            if (isset($departmentPrograms[$deptCode])) {
                foreach ($departmentPrograms[$deptCode] as $programCode) {
                    // Create program if not exists
                    Program::firstOrCreate([
                        'department_id' => $department->id,
                        'code' => $programCode,
                    ]);
                }
            }
        }
    }
}
