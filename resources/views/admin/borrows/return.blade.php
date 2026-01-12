@extends('layouts.admin')

@section('title', 'Return Book')
@section('page-title', 'Return Book')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Return Book Confirmation</h3>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Borrower</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $borrow->user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $borrow->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Book</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $borrow->book->title }}</p>
                        <p class="text-sm text-gray-500">ISBN: {{ $borrow->book->isbn }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Issue Date</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $borrow->borrow_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Due Date</p>
                        <p class="text-lg font-semibold {{ $borrow->due_date < now() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $borrow->due_date->format('M d, Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Return Date</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $returnDate->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Days Overdue</p>
                        <p class="text-lg font-semibold {{ $daysOverdue > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $daysOverdue > 0 ? $daysOverdue . ' day(s)' : 'On Time' }}
                        </p>
                    </div>
                </div>
            </div>

            @if($fineAmount > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium">Fine Amount</p>
                        <p class="text-2xl font-bold text-red-600">₹{{ number_format($fineAmount, 2) }}</p>
                        <p class="text-sm text-red-500 mt-1">{{ $daysOverdue }} day(s) overdue × ₹{{ number_format($fineAmount / max($daysOverdue, 1), 2) }} per day</p>
                    </div>
                    <div class="text-red-600">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">Fine Amount</p>
                        <p class="text-2xl font-bold text-green-600">₹0.00</p>
                        <p class="text-sm text-green-500 mt-1">No fine - Returned on time</p>
                    </div>
                    <div class="text-green-600">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.borrows.return', $borrow) }}">
            @csrf
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.borrows.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Confirm Return
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

