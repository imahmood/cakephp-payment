<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddRrnToPayments extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change()
    {
        $payments = $this->table('payments', [
            'collation' => 'utf8_general_ci',
        ]);

        $payments
            ->addColumn('rrn', 'string', [
                'limit' => 128,
                'default' => null,
                'null' => true,
                'after' => 'tracking_code',
            ]);

        $payments->update();
    }
}
