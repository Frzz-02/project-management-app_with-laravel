@extends('layouts.app')

@section('title', 'Cards Management Test')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Cards Management</h1>
    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Statistics</h2>
        <div class="grid grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded">
                <div class="text-2xl font-bold text-blue-600">{{ $cards->total() }}</div>
                <div class="text-sm text-gray-600">Total Cards</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded">
                <div class="text-2xl font-bold text-green-600">{{ $cards->where('status', 'done')->count() }}</div>
                <div class="text-sm text-gray-600">Done</div>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded">
                <div class="text-2xl font-bold text-yellow-600">{{ $cards->where('status', 'in progress')->count() }}</div>
                <div class="text-sm text-gray-600">In Progress</div>
            </div>
            <div class="text-center p-4 bg-red-50 rounded">
                <div class="text-2xl font-bold text-red-600">{{ $cards->where('priority', 'high')->count() }}</div>
                <div class="text-sm text-gray-600">High Priority</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Cards List</h2>
        </div>
        <div class="p-6">
            @if($cards->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($cards as $card)
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold">{{ $card->card_title }}</h3>
                        <p class="text-sm text-gray-600 mt-2">Status: {{ ucfirst($card->status) }}</p>
                        <p class="text-sm text-gray-600">Priority: {{ ucfirst($card->priority) }}</p>
                        @if($card->due_date)
                        <p class="text-sm text-gray-600">Due: {{ $card->due_date->format('d M Y') }}</p>
                        @endif
                        <div class="mt-4 flex space-x-2">
                            <a href="{{ route('cards.show', $card) }}" class="text-blue-600 hover:underline text-sm">View</a>
                            <a href="{{ route('cards.edit', $card) }}" class="text-green-600 hover:underline text-sm">Edit</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    {{ $cards->links() }}
                </div>
            @else
                <p class="text-gray-500">No cards found.</p>
            @endif
        </div>
    </div>
</div>
@endsection