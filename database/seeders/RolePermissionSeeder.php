<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // PERMISSIONS YARATISH - YANGI ←
        // ============================================
        $permissions = [
            // Faculty
            'view_faculty',
            'create_faculty',
            'edit_faculty',
            'delete_faculty',

            // Department
            'view_department',
            'create_department',
            'edit_department',
            'delete_department',

            // User
            'view_user',
            'create_user',
            'edit_user',
            'delete_user',
            'assign_role',

            // Test
            'view_test',
            'create_test',
            'edit_test',
            'delete_test',
            'take_test',
            'view_test_results',

            // Test Permission
            'manage_test_permissions',

            // Portfolio
            'view_portfolio',
            'upload_portfolio',
            'evaluate_portfolio',
            'view_all_portfolios',

            // Settings
            'manage_settings',

            // Reports
            'view_reports',
            'export_reports',

            // Roles & Permissions
            'manage_roles',
            'manage_permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // ============================================
        // ROLLAR YARATISH VA PERMISSIONS BERISH
        // ============================================

        // Admin Role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all()); // All permissions

        // Prorektor Role
        $prorektorRole = Role::create(['name' => 'prorektor']);
        $prorektorRole->givePermissionTo([
            'view_faculty',
            'view_department',
            'view_user',
            'view_test',
            'view_test_results',
            'manage_test_permissions',
            'view_portfolio',
            'evaluate_portfolio',
            'view_all_portfolios',
            'view_reports',
            'export_reports',
        ]);

        // Teacher Role
        $teacherRole = Role::create(['name' => 'teacher']);
        $teacherRole->givePermissionTo([
            'take_test',
            'view_portfolio',
            'upload_portfolio',
        ]);

        // ============================================
        // YANGI ASR FAKULTETI
        // ============================================
        $faculty = Faculty::create([
            'name' => 'Yangi asr fakulteti',
            'code' => 'YAF',
            'dean_name' => 'Mamatov Dilmurod',
            'phone' => '998901234567',
            'email' => 'dekan@yaf.uz',
            'is_active' => true
        ]);

        // ============================================
        // 6 TA KAFEDRA
        // ============================================
        $departments = [
            [
                'name' => 'Tarix kafedrasi',
                'code' => 'TK',
                'head_name' => 'Rahimov Jamshid',
                'room_number' => '201'
            ],
            [
                'name' => 'Matematika kafedrasi',
                'code' => 'MK',
                'head_name' => 'Karimova Dilnoza',
                'room_number' => '301'
            ],
            [
                'name' => 'Ingliz tili kafedrasi',
                'code' => 'ITK',
                'head_name' => 'Ahmadov Sardor',
                'room_number' => '401'
            ],
            [
                'name' => 'Informatika kafedrasi',
                'code' => 'IK',
                'head_name' => 'Usmonov Aziz',
                'room_number' => '501'
            ],
            [
                'name' => 'Fizika kafedrasi',
                'code' => 'FK',
                'head_name' => 'Toshmatov Bobur',
                'room_number' => '601'
            ],
            [
                'name' => 'Kimyo kafedrasi',
                'code' => 'KK',
                'head_name' => 'Nurmatova Malika',
                'room_number' => '701'
            ],
        ];

        foreach ($departments as $dept) {
            Department::create([
                'faculty_id' => $faculty->id,
                'name' => $dept['name'],
                'code' => $dept['code'],
                'head_name' => $dept['head_name'],
                'room_number' => $dept['room_number'],
                'phone' => '998901234' . rand(500, 599),
                'email' => strtolower($dept['code']) . '@yaf.uz',
                'is_active' => true
            ]);
        }

        // ============================================
        // TEST FOYDALANUVCHILAR
        // ============================================
        $tarixKafedra = Department::where('code', 'TK')->first();
        $matematikaKafedra = Department::where('code', 'MK')->first();
        $ingliztiliKafedra = Department::where('code', 'ITK')->first();

        // 1. ADMIN
        $admin = User::create([
            'full_name' => 'Admin Adminov',
            'faculty_id' => $faculty->id,
            'department_id' => $tarixKafedra->id,
            'passport_series' => 'AA1234567',
            'birth_date' => '1990-01-01',
            'scientific_degree' => 'Professor',
            'phone' => '998901234567',
            'email' => 'admin@yaf.uz',
            'password' => Hash::make('01.01.1990'),
        ]);
        $admin->assignRole($adminRole);

        // 2. PROREKTOR
        $prorektor = User::create([
            'full_name' => 'Yusupov Sherzod Akramovich',
            'faculty_id' => $faculty->id,
            'department_id' => $matematikaKafedra->id,
            'passport_series' => 'AB1234567',
            'birth_date' => '1985-05-15',
            'scientific_degree' => 'Fan doktori',
            'phone' => '998901234568',
            'email' => 'prorektor@yaf.uz',
            'password' => Hash::make('15.05.1985'),
        ]);
        $prorektor->assignRole($prorektorRole);

        // 3-8. O'QITUVCHILAR (Har bir kafedra uchun)
        $teachers = [
            [
                'full_name' => 'Rahimov Jamshid Karimovich',
                'department_id' => $tarixKafedra->id,
                'passport_series' => 'AC1111111',
                'birth_date' => '1992-03-10',
                'scientific_degree' => 'PhD',
            ],
            [
                'full_name' => 'Karimova Dilnoza Akramovna',
                'department_id' => $matematikaKafedra->id,
                'passport_series' => 'AD2222222',
                'birth_date' => '1988-07-22',
                'scientific_degree' => 'Fan nomzodi',
            ],
            [
                'full_name' => 'Ahmadov Sardor Rustamovich',
                'department_id' => $ingliztiliKafedra->id,
                'passport_series' => 'AE3333333',
                'birth_date' => '1995-12-05',
                'scientific_degree' => 'Katta o\'qituvchi',
            ],
            [
                'full_name' => 'Usmonov Aziz Shavkatovich',
                'department_id' => Department::where('code', 'IK')->first()->id,
                'passport_series' => 'AF4444444',
                'birth_date' => '1991-09-18',
                'scientific_degree' => 'Dotsent',
            ],
            [
                'full_name' => 'Toshmatov Bobur Anvarovich',
                'department_id' => Department::where('code', 'FK')->first()->id,
                'passport_series' => 'AG5555555',
                'birth_date' => '1987-11-30',
                'scientific_degree' => 'Professor',
            ],
            [
                'full_name' => 'Nurmatova Malika Zokir qizi',
                'department_id' => Department::where('code', 'KK')->first()->id,
                'passport_series' => 'AH6666666',
                'birth_date' => '1993-04-25',
                'scientific_degree' => 'PhD',
            ],
        ];

        foreach ($teachers as $index => $teacherData) {
            $teacher = User::create([
                'full_name' => $teacherData['full_name'],
                'faculty_id' => $faculty->id,
                'department_id' => $teacherData['department_id'],
                'passport_series' => $teacherData['passport_series'],
                'birth_date' => $teacherData['birth_date'],
                'scientific_degree' => $teacherData['scientific_degree'],
                'phone' => '99890123' . (4570 + $index),
                'email' => 'teacher' . ($index + 1) . '@yaf.uz',
                'password' => Hash::make(date('d.m.Y', strtotime($teacherData['birth_date']))),
            ]);
            $teacher->assignRole($teacherRole);
        }

        $this->command->info('✅ Permissions yaratildi: ' . count($permissions) . ' ta');
        $this->command->info('✅ Rollar yaratildi: admin, prorektor, teacher');
        $this->command->info('✅ Yangi asr fakulteti va 6 ta kafedra yaratildi!');
        $this->command->info('✅ Test foydalanuvchilar:');
        $this->command->info('');
        $this->command->info('👨‍💼 ADMIN:');
        $this->command->info('   Login: AA1234567');
        $this->command->info('   Parol: 01.01.1990');
        $this->command->info('');
        $this->command->info('👨‍🏫 PROREKTOR:');
        $this->command->info('   Login: AB1234567');
        $this->command->info('   Parol: 15.05.1985');
        $this->command->info('');
        $this->command->info('👨‍🎓 O\'QITUVCHILAR:');
        $this->command->info('   1. AC1111111 / 10.03.1992 (Tarix)');

    }
}
