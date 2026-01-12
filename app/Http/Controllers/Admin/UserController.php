<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff,student',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'student_id' => 'nullable|string|max:255|unique:users,student_id',
            'course' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'batch' => 'nullable|string|max:255',
            'semester' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'staff_id' => 'nullable|string|max:255|unique:users,staff_id',
            'department' => 'nullable|string|max:255',
            'staff_role' => 'nullable|in:librarian,assistant',
            'date_of_birth' => 'nullable|date',
            'is_active' => 'boolean',
        ], [
            'email.unique' => 'This email is already registered. Please use another email.',
            'student_id.unique' => 'This student ID is already registered.',
            'staff_id.unique' => 'This staff ID is already registered.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');
        
        // Generate membership ID if not provided
        if (empty($validated['membership_id']) && in_array($validated['role'], ['student', 'staff'])) {
            $validated['membership_id'] = User::generateMembershipId($validated['role']);
        }
        
        // Set member type based on role
        if (empty($validated['member_type'])) {
            if ($validated['role'] === 'student') {
                $validated['member_type'] = 'student';
            } elseif ($validated['role'] === 'staff') {
                $validated['member_type'] = 'staff';
            }
        }
        
        // Set default membership status
        if (empty($validated['membership_status'])) {
            $validated['membership_status'] = 'active';
        }

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->load(['borrows.book', 'fines']);
        
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // If password change is requested, validate current password
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,staff,student',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'student_id' => 'nullable|string|max:255|unique:users,student_id,' . $user->id,
            'staff_id' => 'nullable|string|max:255|unique:users,staff_id,' . $user->id,
            'department' => 'nullable|string|max:255',
            'staff_role' => 'nullable|in:librarian,assistant',
            'date_of_birth' => 'nullable|date',
            'is_active' => 'boolean',
            'membership_id' => 'nullable|string|max:255|unique:users,membership_id,' . $user->id,
            'member_type' => 'nullable|in:student,faculty,staff',
            'membership_status' => 'nullable|in:active,suspended,expired',
            'membership_expiry_date' => 'nullable|date',
        ], [
            'email.unique' => 'This email is already registered. Please use another email.',
            'student_id.unique' => 'This student ID is already registered.',
            'staff_id.unique' => 'This staff ID is already registered.',
            'membership_id.unique' => 'This membership ID is already registered.',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        unset($validated['current_password']);
        $validated['is_active'] = $request->has('is_active');
        
        // Auto-generate membership ID if not provided and role is student/staff
        if (empty($validated['membership_id']) && in_array($validated['role'], ['student', 'staff']) && !$user->membership_id) {
            $validated['membership_id'] = User::generateMembershipId($validated['role']);
        }

        $user->update($validated);

        // If password was changed from modal, redirect back with success
        if ($request->filled('current_password')) {
            return redirect()->back()
                ->with('success', 'Password updated successfully!');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function showChangePassword()
    {
        return view('admin.profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Password updated successfully!');
    }
}
