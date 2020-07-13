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
    public function payRequest(EntityInterface $transaction);

    /**
     * @param \Cake\Datasource\EntityInterface&\CakePayment\Model\Entity\Payment $transaction Transaction
     * @param array $postData Post data
     * @param array $queryParams Query params
     * @return bool
     */
    public function verify(EntityInterface $transaction, array $postData, array $queryParams);

    /**
     * @param string|int $code Response code
     * @return string
     */
    public function getResponseMessage($code);

    /**
     * @param int|null $statusCode status code
     * @return void
     */
    public function connectivityIssue(?int $statusCode = null);

    /**
     * @param string $url url
     * @return void
     */
    public function setCallbackUrl($url);

    /**
     * @return string
     */
    public function getCallbackUrl();

    /**
     * @param string|int $code code
     * @return void
     */
    public function setReferenceCode($code);

    /**
     * @return string|int
     */
    public function getReferenceCode();

    /**
     * @param string|int $code code
     * @return void
     */
    public function setTrackingCode($code);

    /**
     * @return string|int
     */
    public function getTrackingCode();

    /**
     * @param string|int $code code
     * @return void
     */
    public function setResponseCode($code);

    /**
     * @return string|int
     */
    public function getResponseCode();

    /**
     * @param array $data view data
     * @return void
     */
    public function setViewData(array $data);

    /**
     * @return array
     */
    public function getViewData();

    /**
     * @param string $error error message
     * @return void
     */
    public function setError($error);

    /**
     * @return string
     */
    public function getError();
}
