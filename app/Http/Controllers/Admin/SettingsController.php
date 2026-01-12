<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\MemberTypeSetting;
use App\Models\OpeningHour;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    /**
     * Display general settings page
     */
    public function index()
    {
        $fineMapping = \App\Helpers\FineHelper::getFineMapping();
        $memberTypeSettings = MemberTypeSetting::all()->keyBy('member_type');
        $openingHours = OpeningHour::orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")->get();
        $holidays = Holiday::orderBy('date', 'desc')->get();

        return view('admin.settings.index', compact('fineMapping', 'memberTypeSettings', 'openingHours', 'holidays'));
    }

    /**
     * Update general settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'library_name' => 'nullable|string|max:255',
            'library_email' => 'nullable|email|max:255',
            'library_phone' => 'nullable|string|max:20',
            'library_address' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'string');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Display member types settings page
     */
    public function memberTypes()
    {
        $memberTypeSettings = MemberTypeSetting::all()->keyBy('member_type');
        
        // Ensure default settings exist
        $defaults = [
            'student' => ['max_books' => 2, 'duration' => 14, 'fine' => 10.00],
            'faculty' => ['max_books' => 5, 'duration' => 30, 'fine' => 15.00],
            'staff' => ['max_books' => 3, 'duration' => 21, 'fine' => 12.00],
        ];

        foreach ($defaults as $type => $default) {
            if (!$memberTypeSettings->has($type)) {
                MemberTypeSetting::create([
                    'member_type' => $type,
                    'max_books_allowed' => $default['max_books'],
                    'issue_duration_days' => $default['duration'],
                    'fine_per_day' => $default['fine'],
                    'is_active' => true,
                ]);
            }
        }

        $memberTypeSettings = MemberTypeSetting::all()->keyBy('member_type');
        return view('admin.settings.member-types', compact('memberTypeSettings'));
    }

    /**
     * Update member types settings
     */
    public function updateMemberTypes(Request $request)
    {
        $validated = $request->validate([
            'member_types' => 'required|array',
            'member_types.*.max_books_allowed' => 'required|integer|min:1|max:20',
            'member_types.*.issue_duration_days' => 'required|integer|min:1|max:365',
            'member_types.*.fine_per_day' => 'required|numeric|min:0',
            'member_types.*.is_active' => 'boolean',
            'member_types.*.description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['member_types'] as $type => $settings) {
                MemberTypeSetting::updateOrCreate(
                    ['member_type' => $type],
                    [
                        'max_books_allowed' => $settings['max_books_allowed'],
                        'issue_duration_days' => $settings['issue_duration_days'],
                        'fine_per_day' => $settings['fine_per_day'],
                        'is_active' => $settings['is_active'] ?? true,
                        'description' => $settings['description'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('admin.settings.member-types')
            ->with('success', 'Member type settings updated successfully!');
    }

    /**
     * Update fine rules
     */
    public function updateFineRules(Request $request)
    {
        $validated = $request->validate([
            'fine_mapping' => 'required|array',
            'fine_mapping.*.duration' => 'required|integer|min:1|max:365',
            'fine_mapping.*.fine_per_day' => 'required|numeric|min:0',
        ]);

        $mapping = [];
        foreach ($validated['fine_mapping'] as $rule) {
            $mapping[$rule['duration']] = (float) $rule['fine_per_day'];
        }

        \App\Helpers\FineHelper::saveFineMapping($mapping);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Fine rules updated successfully!');
    }

    /**
     * Update opening hours
     */
    public function updateOpeningHours(Request $request)
    {
        $validated = $request->validate([
            'opening_hours' => 'required|array',
            'opening_hours.*.day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'opening_hours.*.opening_time' => 'nullable|date_format:H:i',
            'opening_hours.*.closing_time' => 'nullable|date_format:H:i',
            'opening_hours.*.is_closed' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['opening_hours'] as $hour) {
                OpeningHour::updateOrCreate(
                    ['day_of_week' => $hour['day_of_week']],
                    [
                        'opening_time' => $hour['opening_time'] ?? null,
                        'closing_time' => $hour['closing_time'] ?? null,
                        'is_closed' => $hour['is_closed'] ?? false,
                    ]
                );
            }
        });

        return redirect()->route('admin.settings.index')
            ->with('success', 'Opening hours updated successfully!');
    }

    /**
     * Store a new holiday
     */
    public function storeHoliday(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'description' => 'nullable|string',
        ]);

        Holiday::create($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Holiday added successfully!');
    }

    /**
     * Update a holiday
     */
    public function updateHoliday(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $holiday->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Holiday updated successfully!');
    }

    /**
     * Delete a holiday
     */
    public function deleteHoliday(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Holiday deleted successfully!');
    }
}
