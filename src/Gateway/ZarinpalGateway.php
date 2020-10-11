<?php
declare(strict_types=1);

namespace CakePayment\Gateway;

use Cake\Datasource\EntityInterface;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Http\Exception\HttpException;

class ZarinpalGateway extends AbstractGateway
{
    /**
     * @var string
     */
    public const QUERY_STRING_STATUS_OK = 'OK';

    /**
     * @var string
     */
    public const QUERY_STRING_STATUS_NOT_OK = 'NOK';

    /**
     * @inheritDoc
     */
    protected $_defaultConfig = [
        'merchantCode' => null,
        'sandbox' => true,
    ];

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $server = $this->getConfig('sandbox') ? 'sandbox' : 'www';

        $this->setConfig([
            'serverUrl' => sprintf('https://%s.zarinpal.com/pg/rest/WebGate/PaymentRequest.json', $server),
            'verifyUrl' => sprintf('https://%s.zarinpal.com/pg/rest/WebGate/PaymentVerification.json', $server),
            'redirectUrl' => sprintf('https://%s.zarinpal.com/pg/StartPay/', $server),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function payRequest(EntityInterface $transaction): bool
    {
        $params = [
            'MerchantID' => $this->getConfig('merchantCode'),
            'CallbackURL' => $this->buildCallbackUrl($transaction),
            'Amount' => $transaction->amount,
            'Description' => 'Payment #' . $transaction->id,
        ];

        try {
            $response = $this->httpRequest($this->getConfig('serverUrl'), $params);

            if (!$response->isOk()) {
                $this->connectivityIssue($response->getStatusCode());

                return false;
            }

            $data = $response->getJson();
            $status = (int)array_get($data, 'Status');
            $authority = array_get($data, 'Authority');

            $this->setResponseCode($status);

            if ($status !== 100 || strlen($authority) !== 36) {
                $this->setError($this->getResponseMessage($status));

                return false;
            }

            $this->setReferenceCode($authority);

            $this->setViewData([
                'authority' => $authority,
                'redirectUrl' => $this->getConfig('redirectUrl'),
            ]);

            return true;
        } catch (HttpException $ex) {
            $this->connectivityIssue();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function verify(EntityInterface $transaction, array $postData, array $queryParams): bool
    {
        $status = array_get($queryParams, 'Status');

        if ($status !== self::QUERY_STRING_STATUS_OK) {
            $this->setResponseCode(self::QUERY_STRING_STATUS_NOT_OK);
            $this->setError($this->getResponseMessage(self::QUERY_STRING_STATUS_NOT_OK));

            return false;
        }

        $params = [
            'MerchantID' => $this->getConfig('merchantCode'),
            'Authority' => $transaction->reference_code,
            'Amount' => $transaction->amount,
        ];

        try {
            $response = $this->httpRequest($this->getConfig('verifyUrl'), $params);

            if (!$response->isOk()) {
                $this->connectivityIssue($response->getStatusCode());

                return false;
            }

            $data = $response->getJson();
            $verifyStatus = (int)array_get($data, 'Status');
            $trackingCode = array_get($data, 'RefID');

            $this->setResponseCode($verifyStatus);
            $this->setTrackingCode($trackingCode);

            if ($verifyStatus !== 100) {
                $this->setError($this->getResponseMessage($verifyStatus));

                return false;
            }

            return true;
        } catch (HttpException $ex) {
            $this->connectivityIssue();
        }

        return false;
    }


    /**
     * @return string
     */
    public function getViewFile(): string
    {
        return 'CakePayment.Gateways/zarinpal';
    }

    /**
     * @inheritDoc
     */
    public function getResponseMessage($code): string
    {
        $messages = [
            self::CONNECTION_ERROR => 'برقراری ارتباط با درگاه زرین‌پال میسر نیست.',
            self::QUERY_STRING_STATUS_NOT_OK => 'تراکنش ناموفق بوده و یا توسط کاربر لغو شده است.',
            -1 => 'اطلاعات ارسال شده ناقص است.',
            -2 => 'IP و یا مرچنت کد پذیرنده صحیح نیست.',
            -3 => 'با توجه به محدودیت‌های شاپرک امکان پرداخت با رقم درخواست شده میسر نمی‌باشد.',
            -4 => 'سطح تایید پذیرنده پایین‌تر از سطح نقراه‌ای است.',
            -11 => 'درخواست مورد نظر یافت نشد.',
            -12 => 'امکان ویرایش درخواست میسر نمی‌باشد.',
            -21 => 'هیچ نوع عملیات مالی برای این تراکنش یافت نشد.',
            -22 => 'تراکنش ناموفق می‌باشد.',
            -33 => 'رقم تراکنش با رقم پرداخت شده مطابقت ندارد.',
            -34 => 'سقف تقسیم تراکنش از لحاظ تعداد یا رقم عبور نموده است.',
            -40 => 'اجازه دسترسی به متد مربوطه وجود ندارد.',
            -41 => 'اطلاعات ارسال شده مربوط به AdditionalData غیرمعتبر می‌باشد.',
            -42 => 'مدت زمان معتبر طول عمر شناسه پرداختباید بین ۳۰ دقیقه تا ۴۵ روز باشد.',
            -54 => 'درخواست مورد نظر آرشیو شده است.',
            100 => 'عملیات با موفقیت انجام گردیده است.',
            101 => 'عملیات پرداخت موفق بوده و قبلا PaymentVerification تراکنش انجام شده است.',
        ];

        return $messages[$code] ?? sprintf('کد وضعیت درگاه: %s', $code);
    }

    /**
     * @param string $url url
     * @param array $data data
     * @return \Cake\Http\Client\Response
     */
    protected function httpRequest(string $url, array $data = []): Response
    {
        return (new Client())->post($url, json_encode($data), [
            'type' => 'json',
        ]);
    }
}
