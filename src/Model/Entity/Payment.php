<?php
declare(strict_types=1);

namespace CakePayment\Model\Entity;

use Cake\ORM\Entity;

/**
 * Payment Entity
 *
 * @property int $id
 * @property float $amount
 * @property string|null $reference_code
 * @property string|null $tracking_code
 * @property string|null $response_code
 * @property string|null $card_number
 * @property string $secure_key
 * @property int $status
 * @property \Cake\I18n\FrozenTime|null $completed
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Payment extends Entity
{
    /**
     * @var int
     */
    public const STATUS_PENDING = 0;

    /**
     * @var int
     */
    public const STATUS_START = 1;

    /**
     * @var int
     */
    public const STATUS_APPROVED = 2;

    /**
     * @var int
     */
    public const STATUS_FAILED = 3;

    /**
     * @var int
     */
    public const STATUS_GATEWAY_ERROR = 4;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'amount' => false,
        'reference_code' => false,
        'tracking_code' => false,
        'response_code' => false,
        'card_number' => false,
        'secure_key' => false,
        'status' => false,
        'completed' => false,
        'created' => false,
        'modified' => false,
    ];
}
