<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Product\Actions\SyncProductsAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('products:sync')]
#[Description('Sync products from DummyJSON')]
class SyncProductsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncProductsAction $action): int
    {
        $count = $action->execute();
        $this->info("Successfully synced {$count} products.");
        return self::SUCCESS;
    }
}
