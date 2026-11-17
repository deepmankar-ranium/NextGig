<?php

namespace App\AI;

use Illuminate\Support\Facades\Validator;

class AiOutput
{
    /**
     * Validate AI output against Laravel rules; return only the validated keys.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     *
     * @throws AiException
     */
    public static function validate(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new AiException('invalid_output');
        }

        return $validator->validated();
    }
}
