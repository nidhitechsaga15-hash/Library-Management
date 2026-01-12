<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class PublisherController extends Controller
{
    public function index()
    {
        $publishers = Publisher::withCount('books')->latest()->get();
        return view('admin.publishers.index', compact('publishers'));
    }

    public function create()
    {
        return view('admin.publishers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $publisher = Publisher::create($validated);

        // Audit log
        AuditLog::log('created', $publisher, 'Publisher created');

        return redirect()->route('admin.publishers.index')
            ->with('success', 'Publisher created successfully!');
    }

    public function show(Publisher $publisher)
    {
        $publisher->load('books');
        return view('admin.publishers.show', compact('publisher'));
    }

    public function edit(Publisher $publisher)
    {
        return view('admin.publishers.edit', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $publisher->toArray();
        $validated['is_active'] = $request->has('is_active');
        $publisher->update($validated);

        // Audit log
        AuditLog::log('updated', $publisher, 'Publisher updated', $oldValues, $publisher->toArray());

        return redirect()->route('admin.publishers.index')
            ->with('success', 'Publisher updated successfully!');
    }

    public function destroy(Publisher $publisher)
    {
        if ($publisher->books()->count() > 0) {
            return redirect()->route('admin.publishers.index')
                ->with('error', 'Cannot delete publisher with existing books!');
        }

        // Audit log
        AuditLog::log('deleted', $publisher, 'Publisher deleted');

        $publisher->delete();

        return redirect()->route('admin.publishers.index')
            ->with('success', 'Publisher deleted successfully!');
    }
}
