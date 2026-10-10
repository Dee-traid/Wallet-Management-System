<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
   
    public function change(): void
    {
        $table = $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'string', [
                'limit' => 512,
            ])
            ->addColumn('full_name', 'string', [
                'limit' => 250,
            ])
            ->addColumn('email', 'string', [
                'limit' => 150,
            ])
            ->addColumn('password_hash', 'string', [
                'limit' => 512,
            ])
            ->addColumn('wallet_transaction_pin_hash', 'string', [
                'limit' => 512,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
            ])
            ->addColumn('email_verified', 'boolean', [
                'default' => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
            ])

            ->addIndex(['email'], [
                'unique' => true,
            ])
            ->create();
    }
}
