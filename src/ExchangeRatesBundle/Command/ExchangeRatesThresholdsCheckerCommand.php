<?php

namespace App\ExchangeRatesBundle\Command;

use App\ExchangeRatesBundle\Processor\ExchangeRatesThresholdsCheckerProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExchangeRatesThresholdsCheckerCommand extends Command
{
    protected static $defaultName = 'exchange-rates:exchange-rates-thresholds-checker';

    /** @var ExchangeRatesThresholdsCheckerProcessor */
    private $exchangeRatesThresholdsCheckerProcessor;

    /**
     * @param ExchangeRatesThresholdsCheckerProcessor $exchangeRatesThresholdsCheckerProcessor
     */
    public function __construct(
        ExchangeRatesThresholdsCheckerProcessor $exchangeRatesThresholdsCheckerProcessor
    ) {
        $this->exchangeRatesThresholdsCheckerProcessor = $exchangeRatesThresholdsCheckerProcessor;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->exchangeRatesThresholdsCheckerProcessor->checkExchangeRatesThresholds();

        return 0;
    }
}
