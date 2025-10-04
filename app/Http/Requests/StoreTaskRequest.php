<?php

namespace App\Http\Requests;

use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Card::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'board_id' => 'required|exists:boards,id',
            'card_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:0',
            'created_by' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after:today',
            'status' => 'nullable|in:todo,in progress,review,done',
            'priority' => 'required|in:low,medium,high',
            'estimated_hours' => 'nullable|decimal:5,2|min:0|max:999.99',
            'actual_hours' => 'required|decimal:5,2|min:0|max:999.99',
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
            'board_id.required' => 'Board wajib dipilih.',
            'board_id.exists' => 'Board yang dipilih tidak valid.',
            
            'card_title.required' => 'Judul kartu wajib diisi.',
            'card_title.string' => 'Judul kartu harus berupa teks.',
            'card_title.max' => 'Judul kartu tidak boleh lebih dari 255 karakter.',
            
            'description.string' => 'Deskripsi harus berupa teks.',
            
            'position.integer' => 'Posisi harus berupa angka.',
            'position.min' => 'Posisi tidak boleh bernilai negatif.',
            
            'created_by.exists' => 'Pengguna yang dipilih tidak valid.',
            
            'due_date.date' => 'Tanggal jatuh tempo harus berupa format tanggal yang valid.',
            'due_date.after' => 'Tanggal jatuh tempo harus setelah hari ini.',
            
            'status.in' => 'Status harus salah satu dari: Todo, In Progress, Review, atau Done.',
            
            'priority.required' => 'Prioritas wajib dipilih.',
            'priority.in' => 'Prioritas harus salah satu dari: Low, Medium, atau High.',
            
            'estimated_hours.decimal' => 'Estimasi jam harus berupa angka desimal.',
            'estimated_hours.min' => 'Estimasi jam tidak boleh bernilai negatif.',
            'estimated_hours.max' => 'Estimasi jam tidak boleh lebih dari 999.99 jam.',
            
            'actual_hours.required' => 'Jam aktual wajib diisi.',
            'actual_hours.decimal' => 'Jam aktual harus berupa angka desimal.',
            'actual_hours.min' => 'Jam aktual tidak boleh bernilai negatif.',
            'actual_hours.max' => 'Jam aktual tidak boleh lebih dari 999.99 jam.',
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
            'board_id' => 'board',
            'card_title' => 'judul kartu',
            'description' => 'deskripsi',
            'position' => 'posisi',
            'created_by' => 'dibuat oleh',
            'due_date' => 'tanggal jatuh tempo',
            'status' => 'status',
            'priority' => 'prioritas',
            'estimated_hours' => 'estimasi jam',
            'actual_hours' => 'jam aktual',
        ];
    }
}