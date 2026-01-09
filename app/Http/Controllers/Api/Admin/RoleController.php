<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Get all roles with permissions
     */
    public function index()
    {
        try {
            $roles = Role::with('permissions')->get();

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);

        } catch (\Exception $e) {
            \Log::error('Roles index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Get all permissions
     */
    public function getPermissions()
    {
        try {
            $permissions = Permission::all()->groupBy(function($permission) {
                return explode('_', $permission->name)[1] ?? 'other';
            });

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            \Log::error('Get permissions error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Create role
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:roles,name|max:255',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            $role = Role::create(['name' => $request->name]);

            if ($request->filled('permissions')) {
                $role->givePermissionTo($request->permissions);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rol muvaffaqiyatli yaratildi',
                'data' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            \Log::error('Role store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update role
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            $role->name = $request->name;
            $role->save();

            // Sync permissions
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rol muvaffaqiyatli yangilandi',
                'data' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            \Log::error('Role update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Delete role
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Prevent deleting default roles
            if (in_array($role->name, ['admin', 'prorektor', 'teacher'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asosiy rollarni o\'chirish mumkin emas'
                ], 422);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol muvaffaqiyatli o\'chirildi'
            ]);

        } catch (\Exception $e) {
            \Log::error('Role delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'role' => 'required|exists:roles,name'
            ]);

            $user = \App\Models\User::findOrFail($request->user_id);

            // Remove old roles and assign new one
            $user->syncRoles([$request->role]);

            return response()->json([
                'success' => true,
                'message' => 'Rol muvaffaqiyatli biriktirildi'
            ]);

        } catch (\Exception $e) {
            \Log::error('Assign role error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }
}
