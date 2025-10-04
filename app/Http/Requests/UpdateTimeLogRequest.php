<?php

namespace App\Http\Requests;

use App\Models\TimeLog;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('timeLog'));
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
            'subtask_id' => 'required|exists:subtasks,id',
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'description' => 'nullable|string|max:1000',
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
            
            'subtask_id.required' => 'Subtask wajib dipilih.',
            'subtask_id.exists' => 'Subtask yang dipilih tidak valid.',
            
            'user_id.required' => 'Pengguna wajib dipilih.',
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date' => 'Waktu mulai harus berupa format tanggal dan waktu yang valid.',
            
            'end_time.date' => 'Waktu selesai harus berupa format tanggal dan waktu yang valid.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            
            'duration_minutes.required' => 'Durasi dalam menit wajib diisi.',
            'duration_minutes.integer' => 'Durasi harus berupa angka.',
            'duration_minutes.min' => 'Durasi minimal adalah 1 menit.',
            'duration_minutes.max' => 'Durasi maksimal adalah 1440 menit (24 jam).',
            
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi tidak boleh lebih dari 1000 karakter.',
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
            'start_time' => 'waktu mulai',
            'end_time' => 'waktu selesai',
            'duration_minutes' => 'durasi (menit)',
            'description' => 'deskripsi',
        ];
    }
}
