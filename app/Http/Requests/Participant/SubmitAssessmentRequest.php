<?php

namespace App\Http\Requests\Participant;

use App\Models\Assessment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Assessment|null $assessment */
        $assessment = $this->route('assessment');

        if (! $assessment) {
            return false;
        }

        return $this->user()?->can('update', $assessment) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Assessment $assessment */
        $assessment = $this->route('assessment');
        $questionIds = $assessment->survey->questions()->pluck('id')->toArray();

        $rules = [
            'answers' => ['required', 'array'],
        ];

        foreach ($questionIds as $qId) {
            $rules["answers.{$qId}"] = ['required', 'integer', 'min:1', 'max:10'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'answers.required' => 'All questions must be answered before submitting.',
            'answers.*.required' => 'Please provide a rating between 1 and 10 for each question.',
            'answers.*.min' => 'Minimum score rating is 1.',
            'answers.*.max' => 'Maximum score rating is 10.',
            'answers.*.integer' => 'Ratings must be an integer from 1 to 10.',
        ];
    }
}
