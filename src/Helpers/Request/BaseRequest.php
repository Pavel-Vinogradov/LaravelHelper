<?php

declare(strict_types=1);

namespace Tizix\LaravelHelpers\Helpers\Request;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    abstract public function rules(): array;

    protected function failedValidation(Validator $validator): void
    {
        $errors = (new ValidationException($validator))->errors();
        $firstErrorKey = array_key_first($errors);
        $firstErrorMessage = $errors[$firstErrorKey][0] ?? '';

        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'errors' => [
                    [
                        'type' => 'validation',
                        'message' => $firstErrorMessage,
                        'attribute' => $firstErrorKey,
                    ]
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );

    }
}
