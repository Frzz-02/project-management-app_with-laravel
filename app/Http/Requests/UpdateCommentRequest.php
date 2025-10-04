<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('comment'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'card_id' => 'nullable|exists:cards,id',
            'subtask_id' => 'required|exists:subtasks,id',
            'user_id' => 'required|exists:users,id',
            'comment_text' => 'required|string',
            'comment_type' => 'required|in:card,subtask',
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
            'card_id.exists' => 'Kartu yang dipilih tidak valid.',
            
            'subtask_id.required' => 'Subtask wajib dipilih.',
            'subtask_id.exists' => 'Subtask yang dipilih tidak valid.',
            
            'user_id.required' => 'Pengguna wajib dipilih.',
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            
            'comment_text.required' => 'Teks komentar wajib diisi.',
            'comment_text.string' => 'Teks komentar harus berupa teks.',
            
            'comment_type.required' => 'Tipe komentar wajib dipilih.',
            'comment_type.in' => 'Tipe komentar harus salah satu dari: Card atau Subtask.',
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
            'card_id' => 'kartu',
            'subtask_id' => 'subtask',
            'user_id' => 'pengguna',
            'comment_text' => 'teks komentar',
            'comment_type' => 'tipe komentar',
        ];
    }
}
