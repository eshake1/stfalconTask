<?php

namespace App\ExchangeRatesBundle\Provider;

class PrivatBankExchangeRatesApiUrlProvider
{
    private const EXCHANGE_RATES_API_URL = 'https://api.privatbank.ua/p24api/pubinfo?exchange';

    public function provide(): string
    {
        return self::EXCHANGE_RATES_API_URL;
    }
}
