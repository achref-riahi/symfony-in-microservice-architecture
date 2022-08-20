<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Protobuf\Generated\Currency;
use App\Protobuf\Generated\FinanceClient;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use App\Protobuf\Generated\GetExchangeRateRequest;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

#[AsEventListener(event: KernelEvents::VIEW, method: 'convertPriceCurrency', priority: EventPriorities::PRE_SERIALIZE)]
class ApiPlatformGetProductListener
{
    /** @var FinanceClient */
    private $financeGRPCClient;

    public function __construct(FinanceClient $financeGRPCClient)
    {
        $this->financeGRPCClient = $financeGRPCClient;
    }

    /**
     * Convert price using currency code.
     *
     * @param ViewEvent $event
     * @return void
     */
    public function convertPriceCurrency(ViewEvent $event): void
    {
        $product = $event->getControllerResult();
        $request = $event->getRequest();
        $method = $request->getMethod();
        $currency = (string)$request->query->get('currency');
        if (!$product instanceof Product || Request::METHOD_GET !== $method || !$currency) {
            return;
        }
        $currency = strtoupper($currency);
        if (!$this->isCurrencyValid($currency)) {
            throw new BadRequestException('Bad currency code, only USD and EUR are accepted.');
        }
        $exchangeRate = $this->getExchangeRate($currency);

        $product->setPriceWithRate($exchangeRate);
    }

    /**
     * Check if requested currency is valid.
     *
     * @param string $currency
     * @return boolean
     */
    private function isCurrencyValid(string $currency): bool
    {
        $validation = Validation::createIsValidCallable(new Assert\Choice([
            'choices' => [
                'USD',
                'EUR'
            ],
            'message' => 'Bad currency code, only USD and EUR are accepted.',
        ]));

        return $validation($currency);
    }

    private function getExchangeRate(string $currency): float
    {
        [$getExchangeRateResponse, $mt] = $this->financeGRPCClient->getExchangeRate(
            new GetExchangeRateRequest([
                    'from' => Currency::TND,
                    'to' => constant('\App\Protobuf\Generated\Currency::' . $currency)
            ])
        )->wait();
        if ($mt->code !== \Grpc\STATUS_OK) {
            throw new ServiceUnavailableHttpException(5, 'Currency exchange service cannot be reach.');
        }
        return $getExchangeRateResponse->getRate();
    }
}
