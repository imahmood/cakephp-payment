<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreatePayments extends AbstractMigration
{
    /**
     * @var bool
     */
    public $autoId = false;

    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $payments = $this->table('payments');

        $payments
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'signed' => false,
            ])
            ->addColumn('conversion_rate', 'decimal', [
                'signed' => false,
                'default' => 1,
                'precision' => 15,
                'scale' => 10,
            ])
            ->addColumn('amount', 'decimal', [
                'signed' => false,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('reference_code', 'string', [
                'limit' => 128,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('tracking_code', 'string', [
                'limit' => 128,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('response_code', 'string', [
                'limit' => 128,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('secure_key', 'char', [
                'limit' => 36,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('status', 'smallinteger', [
                'signed' => false,
                'default' => 0,
            ])
            ->addColumn('completed', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime')
            ->addColumn('modified', 'datetime');

        $payments
            ->addPrimaryKey('id');

        $payments->create();
    }
}
