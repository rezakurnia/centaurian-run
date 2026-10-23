<?php

namespace App\Console\Commands;

use App\Services\MidtransService;
use Illuminate\Console\Command;

class ReleaseLapsedPayments extends Command
{
    protected $signature = 'payments:release-lapsed';

    protected $description = 'Cancel unpaid registrations whose Midtrans payment page lapsed, freeing their quota seat';

    public function handle(MidtransService $midtrans): int
    {
        $this->info('Dilepas: '.$midtrans->releaseLapsed());

        return self::SUCCESS;
    }
}
