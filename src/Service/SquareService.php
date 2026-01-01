<?php

namespace App\Service;

use App\Entity\Game;
use App\Entity\User;
use Square\SquareClient;
use Square\Types\Money;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SquareService
{
    private SquareClient $client;
    private string $locationId;

    public function __construct(ParameterBagInterface $params)
    {
        $accessToken = $params->get('square_access_token');
        $this->locationId = $params->get('square_location_id');
        $environment = $params->get('square_environment') === 'production' ? 'production' : 'sandbox';

        $this->client = new SquareClient(
            token: $accessToken,
            options: [
                'baseUrl' => $environment === 'production'
                    ? \Square\Environments::Production->value
                    : \Square\Environments::Sandbox->value,
            ]
        );
    }

    public function createPayment(string $sourceId, int $amount, Game $game, User $user)
    {
        try {
            $idempotencyKey = uniqid('pay_', true);

            error_log('Creating Square payment: Amount=' . $amount . ', Source=' . substr($sourceId, 0, 20) . '...');

            $money = new Money([
                'amount' => $amount,
                'currency' => 'USD'
            ]);

            $request = new \Square\Payments\Requests\CreatePaymentRequest([
                'sourceId' => $sourceId,
                'idempotencyKey' => $idempotencyKey,
                'amountMoney' => $money,
                'locationId' => $this->locationId,
                'note' => 'Purchase: ' . substr($game->getTitle(), 0, 500),
                'referenceId' => 'game_' . $game->getId() . '_user_' . $user->getId()
            ]);

            $response = $this->client->payments->create($request);

            if ($response->getErrors()) {
                $errors = $response->getErrors();
                $errorMessages = array_map(fn($e) => $e->getDetail(), $errors);
                throw new \Exception('Square Payment API Error: ' . implode(', ', $errorMessages));
            }

            $payment = $response->getPayment();
            if (!$payment) {
                throw new \Exception('Square Payment failed: No payment object returned');
            }

            error_log('Square payment successful: ' . $payment->getId());
            return $payment;
        } catch (\Exception $e) {
            error_log('Square Payment Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
