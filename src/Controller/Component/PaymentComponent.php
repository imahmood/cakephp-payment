<?php
declare(strict_types=1);

namespace CakePayment\Controller\Component;

use Cake\Chronos\Chronos;
use Cake\Controller\Component;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
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
     * @return \Cake\Datasource\EntityInterface|\CakePayment\Model\Entity\Payment|false
     */
    public function create($amount, array $data = [])
    {
        if ($amount < 1) {
            return false;
        }

        $transaction = $this->getTable()->newEntity($data);
        $transaction->amount = $amount;
        $transaction->status = Payment::STATUS_PENDING;
        $transaction->secure_key = Text::uuid();

        if ($this->getTable()->save($transaction)) {
            return $transaction;
        }

        return false;
    }

    /**
     * @param \Cake\Datasource\EntityInterface|\CakePayment\Model\Entity\Payment $transaction Transaction
     * @return bool
     * @throws \Exception
     */
    public function pay(EntityInterface $transaction)
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
    public function redirect()
    {
        $controller = $this->getController();
        $controller->set($this->getGateway()->getViewData());
        $controller->viewBuilder()->setTemplatePath('/');

        return $controller->render($this->getViewFile());
    }

    /**
     * @param \Cake\Datasource\EntityInterface|\CakePayment\Model\Entity\Payment $transaction Transaction
     * @param array $postData Post data
     * @param array $queryParams Query params
     * @return bool
     * @throws \Exception
     */
    public function verify(EntityInterface $transaction, array $postData, array $queryParams)
    {
        $response = $this->getGateway()->verify($transaction, $postData, $queryParams);

        $transaction->tracking_code = $this->getGateway()->getTrackingCode();
        $transaction->response_code = $this->getGateway()->getResponseCode();
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
    public function getError()
    {
        return $this->getGateway()->getError();
    }

    /**
     * @return string
     */
    protected function getViewFile()
    {
        $filename = Inflector::underscore(strtolower($this->getConfig('use')));

        return 'CakePayment.Gateways/' . $filename;
    }

    /**
     * @param int $id Transaction id
     * @return \Cake\Datasource\EntityInterface|\CakePayment\Model\Entity\Payment|null
     */
    protected function findTransaction($id)
    {
        return $this->getTable()->find()
            ->where(['id' => $id])
            ->first();
    }

    /**
     * @return \Cake\ORM\Table|\CakePayment\Model\Table\PaymentsTable
     */
    protected function getTable()
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
    protected function getGateway()
    {
        if ($this->_gatewayInstance === null) {
            $config = $this->getConfig();
            $name = 'CakePayment.' . Inflector::classify($config['use']);

            $className = App::className($name, 'Gateway', 'Gateway');
            if (empty($className)) {
                throw new Exception("Could not find {$name} gateway class.");
            }

            $this->_gatewayInstance = new $className($config['options']);
            $this->_gatewayInstance->setCallbackUrl($config['callbackUrl']);
        }

        return $this->_gatewayInstance;
    }
}
