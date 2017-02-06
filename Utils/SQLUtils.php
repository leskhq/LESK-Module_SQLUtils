<?php namespace App\Modules\SQLUtils\Utils;

use App\Exceptions\Handler;
use DB;
use Exception;
use Log;
use PDO;

class SQLUtils
{

    /**
     * Execute the SQL query and return an array of rowset.
     * In most cases on one rowset is returned, meaning that
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
     * @return array             The array of results according to the fetch style.
     *
     *  $sqlStr = "SELECT first_name, last_name FROM employees WHERE email LIKE :Email ORDER BY Email;";
     *	$user = SQLExec('my_database', $sqlStr,
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

            $result = $dbStmt->fetchAll($fetch_style);

            return $result;

        } catch (Exception $e) {
            Log::error("Exception caught running SQLUtils::Exec() with statement [" . $query . "].");
            Log::error("Exception message [" . $e->getMessage() . "].");
            (new Handler(Log::getMonolog()))->report($e);
        } finally {
            if (null !== $dbStmt) {
                $dbStmt->closeCursor();
            }
        }

    }

    /**
     * Retrieves the error messages from an array and flatten then into a string with each message separated by the
     * separator provided.
     *
     * @param $sqlResult The array of error messages.
     * @param string $separator The message separator, defaults to ' | '.
     * @return null|string
     */
    public static function GetErrorMessages($sqlResult, $separator = ' | ')
    {
        try {
            $msg = null;
            if (isset($sqlResult) && (count($sqlResult) > 0 )) {
                foreach ($sqlResult as $key => $val) {
                    if (is_array($val)) {
                        $msg .= self::GetErrorMessages($val, $separator);
                    }
                    else {
                        $msg .= ($msg) ? $separator . $val : $val;
                    }
                }
            }

            return $msg;
        } catch (Exception $e) {
            Log::error("Exception caught running SQLUtils::GetErrorMessages().");
            Log::error("Exception message [" . $e->getMessage() . "].");
            (new Handler(Log::getMonolog()))->report($e);
        } finally {

        }

    }

}
