<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HandleServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'workflow_id' => 'required|integer|exists:workflows,id',
            'document_id' => 'required|integer|exists:documents,id',
            'document_action_id' => 'required|integer|exists:document_actions,id',
            'progress_tracker_id' => 'required|integer|exists:progress_trackers,id',
            'serverState' => 'required',
            'message' => 'sometimes|nullable|string',
            'signature' => 'sometimes|nullable|string',
            'amount' => 'sometimes|nullable|numeric',
            'taxable_amount' => 'sometimes|nullable|numeric',
        ];
    }
}
