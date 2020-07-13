<?php
declare(strict_types=1);

namespace CakePayment\Gateway;

use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;

abstract class AbstractGateway implements GatewayInterface
{
    use InstanceConfigTrait;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Error message.
     *
     * @var string
     */
    protected $_error = null;

    /**
     * Callback url.
     *
     * @var string
     */
    protected $_callbackUrl = null;

    /**
     * Reference code.
     *
     * @var string|int
     */
    protected $_referenceCode = null;

    /**
     * Tracking code.
     *
     * @var string|int
     */
    protected $_trackingCode = null;

    /**
     * Gateway response code.
     *
     * @var string|int
     */
    protected $_responseCode = null;

    /**
     * @var array
     */
    protected $_viewData = [];

    /**
     * Constructor.
     *
     * @param array $config config
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->initialize();
    }

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * @param \Cake\Datasource\EntityInterface&\CakePayment\Model\Entity\Payment $transaction Transaction
     * @return bool
     */
    abstract public function payRequest(EntityInterface $transaction);

    /**
     * @param \Cake\Datasource\EntityInterface&\CakePayment\Model\Entity\Payment $transaction Transaction
     * @param array $postData Post data
     * @param array $queryParams Query params
     * @return bool
     */
    abstract public function verify(EntityInterface $transaction, array $postData, array $queryParams);

    /**
     * @param string|int $code Response code
     * @return string
     */
    abstract public function getResponseMessage($code);

    /**
     * @param \Cake\Datasource\EntityInterface&\CakePayment\Model\Entity\Payment $transaction Transaction
     * @return string
     */
    public function buildCallbackUrl(EntityInterface $transaction)
    {
        return $this->addQueryString($this->getCallbackUrl(), [
            'payment' => $transaction->id,
            'key' => $transaction->secure_key,
        ]);
    }

    /**
     * @param string $url url
     * @param array $queryParams Query params
     * @return string
     */
    public function addQueryString($url, array $queryParams)
    {
        $queryString = http_build_query($queryParams);

        if (mb_strpos($url, '?') !== false) {
            return $url . '&' . $queryString;
        }

        return $url . '?' . $queryString;
    }

    /**
     * @param int|null $statusCode status code
     * @return void
     */
    public function connectivityIssue(?int $statusCode = null)
    {
        $code = self::CONNECTION_ERROR;

        if ($statusCode !== null) {
            $code = '_' . $statusCode;
        }

        $this->setResponseCode($code);
        $this->setError($this->getResponseMessage(self::CONNECTION_ERROR));
    }

    /**
     * @param string $url url
     * @return void
     */
    public function setCallbackUrl($url)
    {
        $this->_callbackUrl = $url;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->_callbackUrl;
    }

    /**
     * @param string|int $code code
     * @return void
     */
    public function setReferenceCode($code)
    {
        $this->_referenceCode = $code;
    }

    /**
     * @return string|int
     */
    public function getReferenceCode()
    {
        return $this->_referenceCode;
    }

    /**
     * @param string|int $code code
     * @return void
     */
    public function setTrackingCode($code)
    {
        $this->_trackingCode = $code;
    }

    /**
     * @return string|int
     */
    public function getTrackingCode()
    {
        return $this->_trackingCode;
    }

    /**
     * @param string|int $code code
     * @return void
     */
    public function setResponseCode($code)
    {
        $this->_responseCode = $code;
    }

    /**
     * @return string|int
     */
    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    /**
     * @param array $data view data
     * @return void
     */
    public function setViewData(array $data)
    {
        $this->_viewData = $data;
    }

    /**
     * @return array
     */
    public function getViewData()
    {
        return $this->_viewData;
    }

    /**
     * @param string $error error message
     * @return void
     */
    public function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }
}
