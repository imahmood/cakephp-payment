<?php
declare(strict_types=1);

namespace CakePayment\Controller\Component;

use Cake\Chronos\Chronos;
use Cake\Controller\Component;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Http\Response;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Inflector;
use CakePayment\Gateway\GatewayInterface;
use CakePayment\Model\Entity\Payment;
use Exception;

class PaymentComponent extends Component
{
    use LocatorAwareTrait;

    /**
     * Gateway instance.
     *
     * @var \CakePayment\Gateway\GatewayInterface
     */
    protected $_gatewayInstance = null;

    /**
     * @var \Cake\ORM\Table|\CakePayment\Model\Table\PaymentsTable
     */
    protected $_transactionsTable = null;

    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'use' => null,
        'callbackUrl' => null,
        'options' => [],
    ];

    /**
     * @param float $amount Transaction amount
     * @param array $data Transaction data
     * @return (\Cake\Datasource\EntityInterface&\CakePayment\Model\Entity\Payment)|false
     */
    public function create(float $amount, array $data = [])
    {
        return $this->getTable()->newTransaction($amount, $data);
    }

    /**
     * @param \Cake\Datasource\EntityInterface|\CakePayment\Model\Entity\Payment $transaction Transaction
     * @return bool
     * @throws \Exception
     */
    public function pay(EntityInterface $transaction): bool
    {
        $response = $this->getGateway()->payRequest($transaction);

        if ($response) {
            $transaction->status = Payment::STATUS_START;
            $transaction->reference_code = $this->getGateway()->getReferenceCode();

            return (bool)$this->getTable()->save($transaction);
        }

        $transaction->status = Payment::STATUS_GATEWAY_ERROR;
        $transaction->response_code = $this->getGateway()->getResponseCode();
        $this->getTable()->save($transaction);

        return false;
    }

    /**
     * @return \Cake\Http\Response
     * @throws \Exception
     */
    public function redirect(): Response
    {
        $controller = $this->getController();
        $controller->set($this->getGateway()->getViewData());
        $controller->viewBuilder()->setTemplatePath('/');

        return $controller->render($this->getGateway()->getViewFile());
    }

    /**
     * @param \Cake\Datasource\EntityInterface&\CakePayment\Model\Entity\Payment $transaction Transaction
     * @param array $postData Post data
     * @param array $queryParams Query params
     * @return bool
     * @throws \Exception
     */
    public function verify(EntityInterface $transaction, array $postData, array $queryParams): bool
    {
        $response = $this->getGateway()->verify($transaction, $postData, $queryParams);

        $referenceCode = $this->getGateway()->getReferenceCode();
        if ($referenceCode !== null) {
            $transaction->reference_code = $referenceCode;
        }

        $transaction->tracking_code = $this->getGateway()->getTrackingCode();
        $transaction->response_code = $this->getGateway()->getResponseCode();
        $transaction->rrn = $this->getGateway()->getRrn();
        $transaction->card_number = $this->getGateway()->getCardNumber();
        $transaction->completed = Chronos::now();

        if ($response) {
            $transaction->status = Payment::STATUS_APPROVED;

            return (bool)$this->getTable()->save($transaction);
        }

        $transaction->status = Payment::STATUS_FAILED;
        $this->getTable()->save($transaction);

        return false;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getError(): string
    {
        return $this->getGateway()->getError();
    }

    /**
     * @return \Cake\Datasource\RepositoryInterface&\CakePayment\Model\Table\PaymentsTable
     */
    protected function getTable(): RepositoryInterface
    {
        if ($this->_transactionsTable === null) {
            $this->_transactionsTable = $this->getTableLocator()->get('CakePayment.Payments');
        }

        return $this->_transactionsTable;
    }

    /**
     * @return \CakePayment\Gateway\GatewayInterface
     * @throws \Exception
     */
    protected function getGateway(): GatewayInterface
    {
        if ($this->_gatewayInstance === null) {
            $config = $this->getConfig();
            $className = $config['use'];

            if (strrpos($className, '\\') === false) {
                $className = 'CakePayment.' . Inflector::camelize($className);
                $className = App::className($className, 'Gateway', 'Gateway');
            }

            if (empty($className)) {
                throw new Exception("Could not find {$config['use']} gateway class.");
            }

            $this->_gatewayInstance = new $className($config['options']);

            if ($config['callbackUrl']) {
                $this->_gatewayInstance->setCallbackUrl($config['callbackUrl']);
            }
        }

        return $this->_gatewayInstance;
    }
}
