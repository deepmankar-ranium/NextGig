<?php

namespace App\Actions\Password;

use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkAction
{
    public function execute(array $data): string
    {
        return Password::sendResetLink($data);
    }
}
