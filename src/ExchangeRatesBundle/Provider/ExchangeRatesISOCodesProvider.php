<?php

namespace App\ExchangeRatesBundle\Provider;

class ExchangeRatesISOCodesProvider
{
    private const EXCHANGE_RATES_ISO_CODES = [
        840 => 'USD',
        978 => 'EUR',
        980 => 'UAH',
    ];

    public function provide(): array
    {
        return self::EXCHANGE_RATES_ISO_CODES;
    }
}
