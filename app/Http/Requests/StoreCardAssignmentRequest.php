<?php

namespace App\Http\Requests;

use App\Models\CardAssignment;
use Illuminate\Foundation\Http\FormRequest;

class StoreCardAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', CardAssignment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'card_id' => 'required|exists:cards,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|in:assigned,in progress,completed',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after:started_at',
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
            'card_id.required' => 'Kartu wajib dipilih.',
            'card_id.exists' => 'Kartu yang dipilih tidak valid.',
            
            'user_id.required' => 'Pengguna wajib dipilih.',
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            
            'role.in' => 'Role harus salah satu dari: Assigned, In Progress, atau Completed.',
            
            'started_at.date' => 'Tanggal mulai harus berupa format tanggal yang valid.',
            
            'completed_at.date' => 'Tanggal selesai harus berupa format tanggal yang valid.',
            'completed_at.after' => 'Tanggal selesai harus setelah tanggal mulai.',
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
            'user_id' => 'pengguna',
            'role' => 'role',
            'started_at' => 'tanggal mulai',
            'completed_at' => 'tanggal selesai',
        ];
    }
}
