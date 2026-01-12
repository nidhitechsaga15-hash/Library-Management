@extends('layouts.admin')

@section('title', 'Fine Settings')
@section('page-title', 'Fine Settings')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configure Fine Amount</h3>
        
        <form method="POST" action="{{ route('admin.fines.settings.update') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label for="fine_per_day" class="block text-sm font-medium text-gray-700">Fine Per Day Amount (₹) *</label>
                    <input type="number" name="fine_per_day" id="fine_per_day" value="{{ old('fine_per_day', $finePerDay) }}" required min="0" step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">This amount will be charged per day for overdue books</p>
                    @error('fine_per_day')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Note:</strong> The fine calculation formula is: <strong>Fine Amount = Days Overdue × Fine Per Day</strong>
                    </p>
                    <p class="text-sm text-blue-600 mt-2">
                        Example: If a book is 5 days overdue and fine per day is ₹{{ number_format($finePerDay, 2) }}, 
                        the total fine will be ₹{{ number_format(5 * $finePerDay, 2) }}
                    </p>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('admin.fines.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection













