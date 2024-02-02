<?php

namespace App\ExchangeRatesBundle\Processor;

use App\CoreBundle\Component\Mailer;
use App\CoreBundle\Model\Api\Curl;
use App\ExchangeRatesBundle\Provider\ExchangeRatesISOCodesProvider;
use App\ExchangeRatesBundle\Provider\MonobankExchangeRatesApiUrlProvider;
use App\ExchangeRatesBundle\Provider\PrivatBankExchangeRatesApiUrlProvider;

class ExchangeRatesThresholdsCheckerProcessor
{
    private const EXCHANGE_RATES_THRESHOLDS = [
        [
            'from' => 'USD',
            'to' => 'UAH',
            'buy' => [
                'min' => 36,
                'max' => 41,
            ],
            'sale' => [
                'min' => 36,
                'max' => 41,
            ],
        ],
        [
            'from' =>'EUR',
            'to' => 'UAH',
            'buy' => [
                'min' => 36,
                'max' => 43,
            ],
            'sale' => [
                'min' => 36,
                'max' => 43,
            ],
        ],
    ];

    /** @var Curl */
    private $curl;

    /** @var Mailer */
    private $mailer;

    /** @var PrivatBankExchangeRatesApiUrlProvider */
    private $privatBankExchangeRatesApiUrlProvider;

    /** @var MonobankExchangeRatesApiUrlProvider */
    private $monobankExchangeRatesApiUrlProvider;

    /** @var ExchangeRatesISOCodesProvider */
    private $exchangeRatesISOCodesProvider;

    /**
     * @param Curl $curl
     * @param Mailer $mailer
     * @param PrivatBankExchangeRatesApiUrlProvider $privatBankExchangeRatesApiUrlProvider
     * @param MonobankExchangeRatesApiUrlProvider $monobankExchangeRatesApiUrlProvider
     * @param ExchangeRatesISOCodesProvider $exchangeRatesISOCodesProvider
     */
    public function __construct(
        Curl $curl,
        Mailer $mailer,
        PrivatBankExchangeRatesApiUrlProvider $privatBankExchangeRatesApiUrlProvider,
        MonobankExchangeRatesApiUrlProvider $monobankExchangeRatesApiUrlProvider,
        ExchangeRatesISOCodesProvider $exchangeRatesISOCodesProvider
    ) {
        $this->curl = $curl;
        $this->mailer = $mailer;
        $this->privatBankExchangeRatesApiUrlProvider = $privatBankExchangeRatesApiUrlProvider;
        $this->monobankExchangeRatesApiUrlProvider = $monobankExchangeRatesApiUrlProvider;
        $this->exchangeRatesISOCodesProvider = $exchangeRatesISOCodesProvider;
    }

    public function checkExchangeRatesThresholds(): void
    {
        $privatBankExchangeRates = $this->curl->sendRequest($this->privatBankExchangeRatesApiUrlProvider->provide());

        foreach ($privatBankExchangeRates as $privatBankExchangeRate) {
            $needleRateThresholds = $this->findNeedleRateThresholds($privatBankExchangeRate['ccy'], $privatBankExchangeRate['base_ccy']);

            if ($needleRateThresholds) {
                $isRateExceedThreshold = $this->isRateExceedThreshold($needleRateThresholds, $privatBankExchangeRate['buy'], $privatBankExchangeRate['sale']);

                if ($isRateExceedThreshold) {
                    $this->sendReportEmail($privatBankExchangeRate['ccy'], $privatBankExchangeRate['base_ccy']);
                }
            }
        }

        $monobankExchangeRates = $this->curl->sendRequest($this->monobankExchangeRatesApiUrlProvider->provide());

        if ($monobankExchangeRates['errorDescription'] ?? null) {
            throw new \InvalidArgumentException($monobankExchangeRates['errorDescription']);
        }

        $exchangeRatesISOCodes = $this->exchangeRatesISOCodesProvider->provide();

        foreach ($monobankExchangeRates as $monobankExchangeRate) {
            $currencyCodeFrom = $exchangeRatesISOCodes[$monobankExchangeRate['currencyCodeA']] ?? null;
            $currencyCodeTo = $exchangeRatesISOCodes[$monobankExchangeRate['currencyCodeB']] ?? null;

            if ($currencyCodeFrom && $currencyCodeTo) {
                $needleRateThresholds = $this->findNeedleRateThresholds($currencyCodeFrom, $currencyCodeTo);

                if ($needleRateThresholds) {
                    $isRateExceedThreshold = $this->isRateExceedThreshold($needleRateThresholds, $monobankExchangeRate['rateBuy'], $monobankExchangeRate['rateSell']);

                    if ($isRateExceedThreshold) {
                        $this->sendReportEmail($currencyCodeFrom, $currencyCodeTo);
                    }
                }
            }
        }
    }

    private function isRateExceedThreshold(array $rateThresholds, float $rateBuy, float $rateSell): bool
    {
        $buyRateThresholds = $rateThresholds['buy'];
        $saleRateThresholds = $rateThresholds['sale'];

        if ($buyRateThresholds['min'] > $rateBuy || $buyRateThresholds['max'] < $rateBuy) {
            return true;
        }

        if ($saleRateThresholds['min'] > $rateSell || $saleRateThresholds['max'] < $rateSell) {
            return true;
        }

        return false;
    }

    private function findNeedleRateThresholds(string $rateFrom, string $rateTo): array
    {
        $needleRateThresholds = [];

        foreach (self::EXCHANGE_RATES_THRESHOLDS as $rateThresholds) {
            if ($rateThresholds['from'] === $rateFrom && $rateThresholds['to'] === $rateTo) {
                $needleRateThresholds = $rateThresholds;
            }
        }

        return $needleRateThresholds;
    }

    private function sendReportEmail(string $rateFrom, string $rateTo): void
    {
        $this->mailer->sendEmail(
            'Exchange rates exceed thresholds report',
            'Exchange rates exceed thresholds report',
            '<p>Exchange rate thresholds from currency "' . $rateFrom . '" to "' .$rateTo . '" is exceeded</p>'
        );
    }
}
