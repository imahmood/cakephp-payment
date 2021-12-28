<?php
declare(strict_types=1);

namespace CakePayment\Gateway;

use Cake\Datasource\EntityInterface;

interface GatewayInterface
{
    /**
     * @var string
     */
    public const CONNECTION_ERROR = 'connection_err';

    /**
     * @param \Cake\Datasource\EntityInterface&\CakePayment\Model\Entity\Payment $transaction Transaction
     * @return bool
     */
    public function payRequest(EntityInterface $transaction): bool;

    /**
     * @param \Cake\Datasource\EntityInterface&\CakePayment\Model\Entity\Payment $transaction Transaction
     * @param array $postData Post data
     * @param array $queryParams Query params
     * @return bool
     */
    public function verify(EntityInterface $transaction, array $postData, array $queryParams): bool;

    /**
     * @param string $url Url
     * @return void
     */
    public function setCallbackUrl(string $url): void;

    /**
     * @return string|int|null
     */
    public function getReferenceCode();

    /**
     * @return string|int|null
     */
    public function getTrackingCode();

    /**
     * @return string|int|null
     */
    public function getRrn();

    /**
     * @return string|int|null
     */
    public function getResponseCode();

    /**
     * @return string|null
     */
    public function getCardNumber(): ?string;

    /**
     * @return string
     */
    public function getViewFile(): string;

    /**
     * @return array
     */
    public function getViewData(): array;

    /**
     * @return string
     */
    public function getError(): string;
}
