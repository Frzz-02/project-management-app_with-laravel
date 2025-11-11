@extends('layouts.app')

@section('title', 'Edit Card - ' . $card->card_title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Card</h1>
            <p class="text-gray-600">Update card: {{ $card->card_title }}</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
            <form action="{{ route('cards.update', $card) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Board Selection -->
                <div class="mb-6">
                    <label for="board_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Board <span class="text-red-500">*</span>
                    </label>
                    <select name="board_id" id="board_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select a board</option>
                        @foreach($boards as $board)
                            <option value="{{ $board->id }}" {{ old('board_id', $card->board_id) == $board->id ? 'selected' : '' }}>
                                {{ $board->project->project_name }} - {{ $board->board_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('board_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Card Title -->
                <div class="mb-6">
                    <label for="card_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Card Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="card_title" id="card_title" value="{{ old('card_title', $card->card_title) }}"
                           placeholder="Enter card title"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    @error('card_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="4"
                              placeholder="Enter card description (optional)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $card->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Due Date -->
                <div class="mb-6">
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Due Date 
                    </label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $card->due_date ? $card->due_date->format('Y-m-d') : '') }}"
                           min="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div class="mb-6">
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        Priority
                    </label>
                    <select name="priority" id="priority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="low" {{ old('priority', $card->priority) == 'low' ? 'selected' : '' }}>
                            🔵 Low
                        </option>
                        <option value="medium" {{ old('priority', $card->priority) == 'medium' ? 'selected' : '' }}>
                            🟡 Medium
                        </option>
                        <option value="high" {{ old('priority', $card->priority) == 'high' ? 'selected' : '' }}>
                            🔴 High
                        </option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estimated Hours -->
                <div class="mb-8">
                    <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-2">
                        Estimated Hours
                    </label>
                    <input type="number" name="estimated_hours" id="estimated_hours" value="{{ old('estimated_hours', $card->estimated_hours) }}"
                           step="0.5" min="0" max="999.99"
                           placeholder="e.g. 8.5"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('estimated_hours')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 border-t border-gray-200 pt-6">
                    <a href="{{ route('cards.show', $card) }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        Update Card
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection