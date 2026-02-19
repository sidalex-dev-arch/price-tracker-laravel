<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\TrackLink;
use Illuminate\Support\Facades\Log;

class ScrapePriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $link;

    public function __construct(TrackLink $link)
    {
        $this->link = $link;
    }

    public function handle(): void
    {
        $client = new Client(
            [
            'timeout'         => 30,
            'connect_timeout' => 10,
            'headers'         => [
                'User-Agent'      => $this->getRandomUserAgent(),
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'uk-UA,uk;q=0.9,en-US;q=0.8,en;q=0.7,ru;q=0.6',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer'         => 'https://www.google.com.ua/',
                'Connection'      => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest'  => 'document',
                'Sec-Fetch-Mode'  => 'navigate',
                'Sec-Fetch-Site'  => 'none',
                'Sec-Fetch-User'  => '?1',
                'DNT'             => '1',
            ],
            'cookies'         => true,
            'verify'          => false,
            'allow_redirects' => true,
            ]
        );

        try {
            $response = $client->get($this->link->url);

            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            $priceText = $this->extractPriceFromJsonLd($crawler)
                ?? $this->extractPriceFromSelectors($crawler);

            if ($priceText) {
                $normalized = str_replace(
                    [' ', ' ', '₴', 'грн', 'UAH', 'uah', ',', '–', '—', 'від', 'до', 'ціни:', 'пропозицій:', 'Порівняти', 'Всього'],
                    ['', '', '', '', '', '', '.', ' ', ' ', ' ', ' ', '', '', '', ''],
                    $priceText
                );

                preg_match_all('/\b\d{4,}\b(?:\.\d+)?/', $normalized, $matches);

                if (!empty($matches[0])) {
                    $possiblePrices = array_map('floatval', $matches[0]);
                    $possiblePrices = array_filter($possiblePrices, fn($p) => $p >= 500 && $p <= 500000);

                    if (!empty($possiblePrices)) {
                        $cleanPrice = min($possiblePrices);

                        $this->link->history()->create(
                            [
                            'price' => $cleanPrice,
                            'raw_price' => $priceText,
                            'checked_at' => now(),
                            ]
                        );

                        $this->link->update(
                            [
                            'last_price' => $cleanPrice,
                            'last_checked_at' => now(),
                            ]
                        );

                        // Проверка снижения цены — сравниваем с предыдущей сохранённой ценой
                        $previousPrice = $this->link->last_price; // старая цена ДО обновления

                        // Проверка, когда цена достигла или стала ниже желаемой
                        if ($this->link->track->target_price > 0 && $cleanPrice <= $this->link->track->target_price) {
                            $difference = $this->link->track->target_price - $cleanPrice;
                            $absDifference = abs($difference);

                            // Чтобы не спамить алертом при каждой проверке, если цена уже была ниже
                            // Проверяем, изменилась ли ситуация по сравнению с предыдущей ценой
                            $previousPrice = $this->link->last_price ?? $cleanPrice;

                            if ($previousPrice > $this->link->track->target_price && $cleanPrice <= $this->link->track->target_price) {
                                Log::info("Цена достигла или стала ниже желаемой на {$difference} грн для {$this->link->url}! Было: {$previousPrice}, стало: {$cleanPrice}");

                                $this->link->update(
                                    [
                                    'alert_triggered_at' => now(),
                                    'alert_difference' => -$difference,  // отрицательное = ниже или равно
                                    ]
                                );
                            }
                        }

                        // if ($cleanPrice < $previousPrice && $previousPrice > 0) {
                        //     $alertPercentage = $this->link->track->alert_percentage ?? 10;
                        //     $dropPercent = (($previousPrice - $cleanPrice) / $previousPrice) * 100;

                        //     if ($dropPercent >= $alertPercentage) {
                        //         Log::info("Цена упала на {$dropPercent}% для {$this->link->url}! Было: {$previousPrice} грн, стало: {$cleanPrice} грн");

                        //         // Сохраняем флаг алерта
                        //         $this->link->update(
                        //             [
                        //             'alert_triggered_at' => now(),
                        //             'alert_percentage_detected' => round($dropPercent, 1),
                        //             ]
                        //         );
                        //     }
                        // }


                        Log::info("Успешно спарсена цена для {$this->link->url}: {$cleanPrice} грн (сырая: {$priceText})");
                        return;
                    }
                }
            }

            Log::warning("Цена не найдена на странице: {$this->link->url}");

        } catch (\Exception $e) {
            Log::error("Ошибка парсинга {$this->link->url}: " . $e->getMessage());
        }

        sleep(rand(45, 90));  // задержка после выполнения
    }

    private function getRandomUserAgent()
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/123.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        ];
        return $agents[array_rand($agents)];
    }

    private function extractPriceFromJsonLd(Crawler $crawler)
    {
        $jsonLdNode = $crawler->filter('script[type="application/ld+json"]');
        if ($jsonLdNode->count()) {
            try {
                $json = json_decode($jsonLdNode->text(), true);
                if (is_array($json)) {
                    if (isset($json['offers']['price'])) { return $json['offers']['price'];
                    }
                    if (isset($json['price'])) { return $json['price'];
                    }
                    if (isset($json['offers'][0]['price'])) { return $json['offers'][0]['price'];
                    }
                }
            } catch (\Exception $e) {
                // плохой JSON
            }
        }
        return null;
    }

    private function extractPriceFromSelectors(Crawler $crawler)
{
    $selectors = [
        // Samsung.com (fr)
        'span.pd-buying-price__new-price-currency',
        'div.pd-buying-price__new-price span.pd-buying-price__new-price-currency',
        '#sgDevPriceArea span.pd-buying-price__new-price-currency',
        'div.pd-buying-price__new-price',
        '.pd-buying-price__new-price-currency',

        // Rozetka
        'p.product-price__big',
        'p.product-price__big.text-2xl',
        'p.product-price__small',
        '.product-price__big',

        // Hotline
        '.many__price-sum span.xOfqOvLiWqILDzv6k4FW',
        '.many__price-sum',

        // Allo
        '.a-product-price__current-price',
        '.a-product-price__current',

        // Citrus
        '.price-amount',
        '.product-price .amount',
        '.price',

        // Общий fallback
        '[itemprop="price"]',
        'meta[property="product:price:amount"]',
        '.price',
        '.amount',
        'span[data-main-price]',
        '[class*="price"]',
    ];

    foreach ($selectors as $sel) {
        $node = $crawler->filter($sel);
        if ($node->count()) {
            $text = trim($node->text() ?: $node->attr('content') ?: $node->attr('value') ?: '');
            if ($text && preg_match('/\d/', $text)) {
                if (preg_match('/пропозицій|ціни:|Всього|Порівняти|скидка|discount|old-price/i', $text)) {
                    continue;
                }
                return $text;
            }
        }
    }

    return null;
}
}