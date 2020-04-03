<?php
declare(strict_types=1);

namespace CakePayment\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Payments Model
 *
 * @method \CakePayment\Model\Entity\Payment newEmptyEntity()
 * @method \CakePayment\Model\Entity\Payment newEntity(array $data, array $options = [])
 * @method \CakePayment\Model\Entity\Payment[] newEntities(array $data, array $options = [])
 * @method \CakePayment\Model\Entity\Payment get($primaryKey, $options = [])
 * @method \CakePayment\Model\Entity\Payment findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \CakePayment\Model\Entity\Payment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CakePayment\Model\Entity\Payment[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \CakePayment\Model\Entity\Payment|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CakePayment\Model\Entity\Payment saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CakePayment\Model\Entity\Payment[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \CakePayment\Model\Entity\Payment[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \CakePayment\Model\Entity\Payment[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \CakePayment\Model\Entity\Payment[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PaymentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('payments');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->decimal('amount')
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount');

        $validator
            ->maxLength('reference_code', 128)
            ->allowEmptyString('reference_code');

        $validator
            ->maxLength('tracking_code', 128)
            ->allowEmptyString('tracking_code');

        $validator
            ->maxLength('response_code', 128)
            ->allowEmptyString('response_code');

        $validator
            ->uuid('secure_key')
            ->requirePresence('secure_key', 'create')
            ->notEmptyString('secure_key');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmptyString('status');

        $validator
            ->dateTime('completed')
            ->allowEmptyDateTime('completed');

        return $validator;
    }

    /**
     * @param \Cake\Database\Schema\TableSchemaInterface $schema The table definition fetched from database.
     * @return \Cake\Database\Schema\TableSchemaInterface the altered schema
     */
    protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
    {
        return $schema->setColumnType('status', 'integer');
    }
}
