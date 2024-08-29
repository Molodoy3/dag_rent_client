<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class SendMessageData extends Data
{
    public function __construct(
        #[Max(2000)]
        public string $message,
    ) {
    }
}
