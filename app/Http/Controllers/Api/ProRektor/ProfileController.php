<?php

namespace App\Http\Controllers\Api\ProRektor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get current user profile
     */
    public function show()
    {
        try {
            $user = auth()->user()->load(['faculty', 'department', 'roles']);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            \Log::error('ProRektor profile show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        try {
            $user = auth()->user();

            \Log::info('ProRektor profile update', [
                'user_id' => $user->id,
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar && file_exists(public_path($user->avatar))) {
                    unlink(public_path($user->avatar));
                }

                $avatar = $request->file('avatar');
                $avatarName = time() . '_' . $avatar->getClientOriginalName();
                $avatar->move(public_path('avatars'), $avatarName);
                $validated['avatar'] = 'avatars/' . $avatarName;
            }

            $user->update($validated);

            \Log::info('ProRektor profile updated', [
                'user_id' => $user->id,
                'full_name' => $user->full_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil yangilandi',
                'data' => $user->fresh(['faculty', 'department', 'roles'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatoligi',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('ProRektor profile update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => ['required', 'confirmed', Password::min(8)],
            ]);

            // Verify current password
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Joriy parol noto\'g\'ri',
                    'errors' => ['current_password' => ['Joriy parol noto\'g\'ri']]
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);

            \Log::info('ProRektor password changed', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parol muvaffaqiyatli o\'zgartirildi'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatoligi',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('ProRektor change password error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }
}
