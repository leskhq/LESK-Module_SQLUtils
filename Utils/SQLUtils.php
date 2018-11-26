<?php namespace App\Modules\SQLUtils\Utils;

use App\Exceptions\Handler;
use App\Libraries\Str;
use App\Modules\SQLUtils\Exceptions\SQLException;
use DB;
use Exception;
use Log;
use PDO;

class SQLUtils
{

    /**
     * Execute the SQL query and returns an array of rowset.
     * In most cases only one rowset is returned, meaning that
     * only rowSets[0] be set and contain all the rows that
     * really matter.
     *
     * @param $connectionName 	 The name of the connection to use as configured in 'database.php'
     * @param $query 			 The SQL query to run.
     * @param null $params		 An array of parameters for the query. Either positional or named.
     * 							 The key of the array is the position or name, the value is an
     * 							 other array containing both the value with the 'data' key and the
     * 							 type.
     * @param int $fetch_style   The fetch style for the query.
     * @param null $stmtOptions  Statement options to run before the real query.
     * @param \Illuminate\Database\Connection $dbConn  The database connection to use, otherwise a new one
     *                           will be established. Useful with transactions.
     * @param boolean $multiRowset Indicates that we expect the result to contain multiple rowsets.
     * @return array             The array of results according to the fetch style.
     *
     *  $sqlStr = "SELECT first_name, last_name FROM employees WHERE email LIKE :Email ORDER BY Email;";
     *	$user = SQLUtils::ExecPrefetch('my_database', $sqlStr,
     *		    [
     *			    ':Email' => ['data' => $emailStr, 'type' => PDO::PARAM_STR],
     * 		    ],
     *          PDO::FETCH_OBJ,
     *          [
     *              "SET ANSI_NULLS ON",
     *              "SET ANSI_WARNINGS ON",
     *          ]);
     *  $FN = $user->first_name;
     *  $LN = user->last_name;
     *
     */
    public static function ExecPrefetch($connectionName, $query, $params = null, $fetch_style = PDO::FETCH_BOTH, $stmtOptions = null, \Illuminate\Database\Connection $dbConn = null)
    {
        $dbStmt = null;

        try {

            $dbStmt = SQLUtils::Exec($connectionName, $query, $params, $fetch_style, $stmtOptions, $dbConn);

            // Get all result sets.
            $cnt = 0;
            do {
                $rowset = $dbStmt->fetchAll($fetch_style);
                $result[$cnt] = $rowset;
                $cnt++;
            } while ($dbStmt->nextRowset() && $dbStmt->columnCount());

            // If we only got one result set, pick that one and return it.
            if (1 == count($result)) {
                $result = $result[0];
            }

            // Check if the query issued an internal error.
            $errorInfo =  self::GetDBOErrorInfo($dbStmt);
            if (null != $errorInfo)
            {
                throw new SQLException($errorInfo, $result);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Exception caught running SQLUtils::ExecPrefetch() with statement [" . $query . "].");
            Log::error("Exception message [" . $e->getMessage() . "].");
            (new Handler(Log::getMonolog()))->report($e);
            throw $e;
        } finally {
            if (null !== $dbStmt) {
                $dbStmt->closeCursor();
            }
        }

    }

    /**
     * Execute the SQL query and returns the raw PDOStatement.
     *
     * @param $connectionName 	 The name of the connection to use as configured in 'database.php'
     * @param $query 			 The SQL query to run.
     * @param null $params		 An array of parameters for the query. Either positional or named.
     * 							 The key of the array is the position or name, the value is an
     * 							 other array containing both the value with the 'data' key and the
     * 							 type.
     * @param int $fetch_style   The fetch style for the query.
     * @param null $stmtOptions  Statement options to run before the real query.
     * @param \Illuminate\Database\Connection $dbConn  The database connection to use, otherwise a new one
     *                           will be established. Useful with transactions.
     * @param boolean $multiRowset Indicates that we expect the result to contain multiple rowsets.
     * @return array             The array of results according to the fetch style.
     *
     *
     */
    public static function Exec($connectionName, $query, $params = null, $fetch_style = PDO::FETCH_BOTH, $stmtOptions = null, \Illuminate\Database\Connection $dbConn = null)
    {
        $dbStmt = null;

        try {

            if ( null === $dbConn ) {
                $dbConn = DB::connection($connectionName);
            }

            // If any
            if (isset($stmtOptions) and count($stmtOptions) > 0) {
                foreach ($stmtOptions as $opt) {
                    $dbConn->getPdo()->query($opt);
                }
            }

            $dbPdo = $dbConn->getPdo();

            $dbStmt = $dbPdo->prepare($query);

            if (isset($params) and count($params) > 0) {
                foreach ($params as $key => $val) {
                    $dbStmt->bindValue($key, $val['data'], $val['type']);
                }
            }

            $dbStmt->execute();

            return $dbStmt;

        } catch (Exception $e) {
            Log::error("Exception caught running SQLUtils::Exec() with statement [" . $query . "].");
            Log::error("Exception message [" . $e->getMessage() . "].");
            (new Handler(Log::getMonolog()))->report($e);
            throw $e;
        }

    }

    /**
     * Deprecated function, do not use.
     * Instead use the function FlattenArrayOfMessages.
     *
     * @deprecated Use FlattenArrayOfMessages instead.
     *
     * @param $sqlResult The array of error messages.
     * @param string $separator The message separator, defaults to ' | '.
     * @return null|string
     */
    public static function GetErrorMessages($sqlResult, $separator = ' | ')
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        self::FlattenArrayOfMessages($sqlResult, $separator);
    }

    /**
     * Retrieves the error messages from an array and flatten then into a string with each message separated by the
     * separator provided.
     *
     * @param $sqlResult The array of error messages.
     * @param string $separator The message separator, defaults to ' | '.
     * @return null|string
     */
    public static function FlattenArrayOfMessages($sqlResult, $separator = ' | ')
    {
        try {
            $msg = null;
            if (isset($sqlResult) && (count($sqlResult) > 0 )) {
                foreach ($sqlResult as $key => $val) {
                    if (is_array($val)) {
                        $msg .= self::FlattenArrayOfMessages($val, $separator);
                    }
                    else {
                        $msg .= ($msg) ? $separator . $val : $val;
                    }
                }
            }

            return $msg;
        } catch (Exception $e) {
            Log::error("Exception caught running SQLUtils::FlattenArrayOfMessages().");
            Log::error("Exception message [" . $e->getMessage() . "].");
            (new Handler(Log::getMonolog()))->report($e);
        } finally {

        }

    }

    /**
     * Get the error info array from the last executed statement.
     * Returns null is the error info array contains status values of 0,
     * meaning that the last statement was successful.
     *
     * @param $dbStmt
     * @return mixed
     */
    private static function GetDBOErrorInfo($dbStmt)
    {
        $dboErrorInfo = null;

        // Get the DBO error info array.
        $dboErrorInfo = $dbStmt->errorInfo();
        // If error info array is not null.
        if (!Str::isNullOrEmptyString($dboErrorInfo)) {
            // Extract SQL State and Driver Code.
            $sqlState = $dboErrorInfo[0];
            $sqlDriverCode = $dboErrorInfo[1];
            // If SQL State or Driver Code are zero.
            if (('00000' == $sqlState) && (0 == $sqlDriverCode)) {
                // Set the return variable to null, as every thing worked as expected.
                $dboErrorInfo = null;
            }
        }

        // Return Message or null if last statement was successful
        return $dboErrorInfo;
    }

}
