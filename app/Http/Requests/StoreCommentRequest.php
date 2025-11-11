<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Comment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'card_id' => 'required_without:subtask_id|exists:cards,id',
            'subtask_id' => 'required_without:card_id|exists:subtasks,id',
            'content' => 'required|string|max:2000',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'card_id.required_without' => 'Card ID is required when subtask ID is not provided.',
            'card_id.exists' => 'The selected card is invalid.',
            
            'subtask_id.required_without' => 'Subtask ID is required when card ID is not provided.',
            'subtask_id.exists' => 'The selected subtask is invalid.',
            
            'content.required' => 'Comment content is required.',
            'content.string' => 'Comment content must be a string.',
            'content.max' => 'Comment content must not exceed 2000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'card_id' => 'card',
            'subtask_id' => 'subtask',
            'content' => 'comment content',
        ];
    }
}
