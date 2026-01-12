<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Library;
use App\Models\LibrarySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    public function index()
    {
        $libraries = Library::with(['staff', 'settings'])->latest()->get();
        return view('admin.libraries.index', compact('libraries'));
    }

    public function create()
    {
        $staff = User::where('role', 'staff')->get();
        return view('admin.libraries.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:libraries,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'staff_id' => 'nullable|exists:users,id',
            'book_issue_duration_days' => 'nullable|integer|min:1|max:365',
            'book_collection_deadline_days' => 'nullable|integer|min:1|max:30',
            'max_books_per_student' => 'nullable|integer|min:1',
            'max_books_per_subject' => 'nullable|integer|min:1',
            'fine_per_day' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $library = Library::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            // Attach single staff
            if (!empty($validated['staff_id'])) {
                $library->staff()->attach($validated['staff_id']);
            }

            // Create settings
            LibrarySetting::create([
                'library_id' => $library->id,
                'staff_id' => $validated['staff_id'] ?? null,
                'book_issue_duration_days' => $validated['book_issue_duration_days'] ?? 14,
                'book_collection_deadline_days' => $validated['book_collection_deadline_days'] ?? 2,
                'max_books_per_student' => $validated['max_books_per_student'] ?? 2,
                'max_books_per_subject' => $validated['max_books_per_subject'] ?? 1,
                'fine_per_day' => $validated['fine_per_day'] ?? 5.00,
            ]);
        });

        return redirect()->route('admin.libraries.index')
            ->with('success', 'Library created successfully!');
    }

    public function show(Library $library)
    {
        $library->load(['staff', 'settings', 'books']);
        return view('admin.libraries.show', compact('library'));
    }

    public function edit(Library $library)
    {
        $library->load(['staff', 'settings']);
        $staff = User::where('role', 'staff')->get();
        return view('admin.libraries.edit', compact('library', 'staff'));
    }

    public function update(Request $request, Library $library)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:libraries,code,' . $library->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'staff_id' => 'nullable|exists:users,id',
            'book_issue_duration_days' => 'nullable|integer|min:1|max:365',
            'book_collection_deadline_days' => 'nullable|integer|min:1|max:30',
            'max_books_per_student' => 'nullable|integer|min:1',
            'max_books_per_subject' => 'nullable|integer|min:1',
            'fine_per_day' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $library) {
            $library->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Sync single staff
            if (isset($validated['staff_id'])) {
                if ($validated['staff_id']) {
                    $library->staff()->sync([$validated['staff_id']]);
                } else {
                    $library->staff()->detach();
                }
            }

            // Update or create settings
            $settings = $library->settings ?? new LibrarySetting(['library_id' => $library->id]);
            $settings->update([
                'staff_id' => $validated['staff_id'] ?? null,
                'book_issue_duration_days' => $validated['book_issue_duration_days'] ?? $settings->book_issue_duration_days ?? 14,
                'book_collection_deadline_days' => $validated['book_collection_deadline_days'] ?? $settings->book_collection_deadline_days ?? 2,
                'max_books_per_student' => $validated['max_books_per_student'] ?? $settings->max_books_per_student ?? 2,
                'max_books_per_subject' => $validated['max_books_per_subject'] ?? $settings->max_books_per_subject ?? 1,
                'fine_per_day' => $validated['fine_per_day'] ?? $settings->fine_per_day ?? 5.00,
            ]);
        });

        return redirect()->route('admin.libraries.index')
            ->with('success', 'Library updated successfully!');
    }

    public function destroy(Library $library)
    {
        if ($library->books()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete library with books! Please remove books first.');
        }

        $library->delete();
        return redirect()->route('admin.libraries.index')
            ->with('success', 'Library deleted successfully!');
    }

    public function settings(Library $library)
    {
        $library->load('settings');
        $settings = $library->settings ?? new LibrarySetting(['library_id' => $library->id]);
        return view('admin.libraries.settings', compact('library', 'settings'));
    }

    public function updateSettings(Request $request, Library $library)
    {
        $validated = $request->validate([
            'book_issue_duration_days' => 'required|integer|min:1|max:365',
            'book_collection_deadline_days' => 'required|integer|min:1|max:30',
            'max_books_per_student' => 'required|integer|min:1',
            'max_books_per_subject' => 'required|integer|min:1',
            'fine_per_day' => 'required|numeric|min:0',
            'almirah_config' => 'nullable|json',
        ]);

        $settings = $library->settings ?? new LibrarySetting(['library_id' => $library->id]);
        $settings->fill($validated);
        $settings->save();

        return redirect()->route('admin.libraries.settings', $library)
            ->with('success', 'Library settings updated successfully!');
    }
}
