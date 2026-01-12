<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,staff,student',
            'student_id' => 'nullable|string|max:255|unique:users,student_id',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'date_of_birth' => 'required|date',
            'course' => $request->role === 'student' ? 'required|string|max:255' : 'nullable|string|max:255',
            'branch' => $request->role === 'student' ? 'required|string|max:255' : 'nullable|string|max:255',
            'batch' => 'nullable|string|max:255',
            'semester' => $request->role === 'student' ? 'required|string|max:255' : 'nullable|string|max:255',
            'year' => $request->role === 'student' ? 'required|string|max:255' : 'nullable|string|max:255',
        ], [
            'email.unique' => 'This email is already registered. Please use another email.',
            'student_id.unique' => 'This student ID is already registered.',
            'father_name.required' => 'Please enter your father\'s name.',
            'mother_name.required' => 'Please enter your mother\'s name.',
            'address.required' => 'Please enter your address.',
            'date_of_birth.required' => 'Please enter your date of birth.',
            'course.required' => 'Please select your course.',
            'branch.required' => 'Please select your branch.',
            'semester.required' => 'Please select your semester.',
            'year.required' => 'Please select your year.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'student_id' => $request->student_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'course' => $request->course,
            'branch' => $request->branch,
            'batch' => $request->batch,
            'semester' => $request->semester,
            'year' => $request->year,
        ]);

        Auth::login($user);

        // Redirect based on role
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard');
        } elseif ($user->isStaff()) {
            return redirect('/staff/dashboard');
        } else {
            return redirect('/student/dashboard');
        }
    }
}
