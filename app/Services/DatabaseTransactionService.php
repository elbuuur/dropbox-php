<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DatabaseTransactionService
{
    /**
     * Executes database operations within a transaction.
     *
     * @param callable $operations An anonymous function containing database operations
     *                            that should be executed within a transaction.
     *
     * @return mixed The result of executing the database operations if they are successful.
     * @throws \Exception In case of an error during the operations execution, the transaction is rolled back,
     *                    and the exception is thrown for further handling.
     */
    public function performTransaction(callable $operations)
    {
        DB::beginTransaction();

        try {
            $result = $operations();

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
