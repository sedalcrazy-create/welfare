<?php

namespace App\Events;

use App\Models\Personnel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when personnel request is rejected
 */
class PersonnelRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Personnel $personnel,
        public string $reason
    ) {}
}
