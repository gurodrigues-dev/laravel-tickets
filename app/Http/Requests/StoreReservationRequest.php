<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required|integer|exists:events,id',
            'quantity' => 'required|integer|min:1|max:999',
            'version' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.required' => 'The event ID is required.',
            'event_id.integer' => 'The event ID must be an integer.',
            'event_id.exists' => 'The selected event does not exist.',
            'quantity.required' => 'The quantity field is required.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min' => 'The quantity must be at least 1.',
            'quantity.max' => 'The quantity cannot exceed 999.',
            'version.required' => 'The version field is required.',
            'version.integer' => 'The version must be an integer.',
            'version.min' => 'The version must be at least 1.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
