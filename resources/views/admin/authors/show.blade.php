@extends('layouts.admin')

@section('title', 'Author Details')
@section('page-title', 'Author Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">{{ $author->name }}</h3>
                <p class="text-gray-500 mt-1">{{ $author->nationality ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('admin.authors.edit', $author) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Edit
            </a>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-sm text-gray-500">Date of Birth</p>
                <p class="text-lg font-semibold">{{ $author->date_of_birth ? $author->date_of_birth->format('F d, Y') : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Books</p>
                <p class="text-lg font-semibold">{{ $author->books->count() }}</p>
            </div>
        </div>

        @if($author->bio)
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-2">Biography</p>
            <p class="text-gray-700">{{ $author->bio }}</p>
        </div>
        @endif

        <div class="border-t pt-6">
            <h4 class="text-lg font-semibold mb-4">Books by {{ $author->name }}</h4>
            @if($author->books->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($author->books as $book)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $book->title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $book->category->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $book->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($book->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.books.show', $book) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500">No books found for this author.</p>
            @endif
        </div>
    </div>
</div>
@endsection

















