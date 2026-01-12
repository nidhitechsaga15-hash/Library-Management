<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:admin,staff,student',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', $request->role)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Create simple API token (you can use Sanctum later)
        $token = base64_encode($user->id . '|' . $user->email . '|' . now()->timestamp);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'course' => $user->course,
                'year' => $user->year,
                'batch' => $user->batch,
                'semester' => $user->semester,
            ],
        ]);
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
            'captcha_token' => 'required|string',
        ], [
            'email.unique' => 'This email is already registered.',
            'student_id.unique' => 'This student ID is already registered.',
            'father_name.required' => 'Please enter your father\'s name.',
            'mother_name.required' => 'Please enter your mother\'s name.',
            'address.required' => 'Please enter your address.',
            'date_of_birth.required' => 'Please enter your date of birth.',
            'course.required' => 'Please select your course.',
            'branch.required' => 'Please select your branch.',
            'semester.required' => 'Please select your semester.',
            'year.required' => 'Please select your year.',
            'captcha_token.required' => 'Please complete the captcha.',
        ]);

        // Verify captcha (you can add Google reCAPTCHA verification here)
        // For now, we'll just check if token exists

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

        // Create simple API token
        $token = base64_encode($user->id . '|' . $user->email . '|' . now()->timestamp);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'course' => $user->course,
                'year' => $user->year,
                'batch' => $user->batch,
                'semester' => $user->semester,
            ],
        ]);
    }

    public function profile(Request $request)
    {
        // Get user from token
        $token = $request->bearerToken() ?? $request->header('Authorization');
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Decode token and get user
        $decoded = base64_decode($token);
        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }
        
        $user = User::find($parts[0]);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 401);
        }
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'course' => $user->course,
                'branch' => $user->branch,
                'batch' => $user->batch,
                'semester' => $user->semester,
                'year' => $user->year,
            ],
        ]);
    }
}
