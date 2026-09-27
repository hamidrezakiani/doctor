<?php

namespace App\Console\Commands;

use App\Domains\Order\Actions\DeliverExpiredOrdersAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:deliver-expired')]
#[Description('Deliver shipped orders older than 24 hours')]
class DeliverExpiredOrdersCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DeliverExpiredOrdersAction $action): int
    {
        $count = $action->execute();

        $this->info(
            "Successfully delivered {$count} expired order(s).",
        );

        return self::SUCCESS;
    }
}
