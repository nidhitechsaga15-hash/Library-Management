<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Borrow;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'student');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('student_id', 'like', '%' . $request->search . '%');
            });
        }

        $students = $query->latest()->get();

        return view('staff.students.index', compact('students'));
    }

    public function show(User $user)
    {
        $user->load(['borrows.book', 'fines']);
        $activeBorrows = Borrow::where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->with('book')
            ->get();

        return view('staff.students.show', compact('user', 'activeBorrows'));
    }

    public function issueHistory(User $user)
    {
        $user->load(['borrows.book', 'fines.borrow.book']);
        
        $allBorrows = Borrow::where('user_id', $user->id)
            ->with(['book', 'fine'])
            ->latest()
            ->paginate(20);

        return view('staff.students.issue-history', compact('user', 'allBorrows'));
    }
}
