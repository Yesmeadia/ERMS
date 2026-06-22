<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Services\VirusScanner;
use Illuminate\Http\UploadedFile;

class VirusFree implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value instanceof UploadedFile) {
            $scanner = new VirusScanner();
            if (!$scanner->scan($value)) {
                $fail('The uploaded file contains a security threat or has an invalid/malicious structure.');
            }
        }
    }
}
