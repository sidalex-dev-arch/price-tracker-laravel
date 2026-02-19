<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrackLink;
use App\Jobs\ScrapePriceJob;

class ScrapeAllPrices extends Command
{
    protected $signature = 'prices:scrape-all';
    protected $description = 'Собрать актуальные цены по всем трекам';

    public function handle()
    {
        $links = TrackLink::where(function ($q) {
            $q->whereNull('last_checked_at')
              ->orWhere('last_checked_at', '<', now()->subHours(4));
        })->get();

        $this->info("Найдено {$links->count()} ссылок для проверки");

        foreach ($links as $link) {
            ScrapePriceJob::dispatch($link);
            $this->info("Задача отправлена для: {$link->url}");
        }

        $this->info('Все задачи отправлены в очередь.');
    }
}