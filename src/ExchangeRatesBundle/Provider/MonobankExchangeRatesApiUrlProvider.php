<?php

namespace App\ExchangeRatesBundle\Provider;

class MonobankExchangeRatesApiUrlProvider
{
    private const EXCHANGE_RATES_API_URL = 'https://api.monobank.ua/bank/currency';

    public function provide(): string
    {
        return self::EXCHANGE_RATES_API_URL;
    }
}
