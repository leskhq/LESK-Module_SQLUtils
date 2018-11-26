<?php namespace App\Modules\SQLUtils\Exceptions;

class SQLException extends \Exception
{
    /**
     * The PDO Error Information array.
     *
     * Element	Information
     * 0	SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
     * 1	Driver specific error code.
     * 2	Driver specific error message.
     *
     * @var array
     */
    protected $errorInfo;

    /**
     * The data returned by the last query if there is any.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param array      $errorInfo
     * @param mixed      $data
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($errorInfo, $data = null, $code = 0, \Exception $previous = null)
    {
        $this->errorInfo = $errorInfo;
        $this->data      = $data;

        parent::__construct('SQL exception occurred with message "' . $this->errorInfo[2] . '" and SQLState "' . $this->errorInfo[0] . '".', $code, $previous);
    }

    /**
     * Get the error info array.
     *
     * @return array
     */
    public function getErrorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * Get the data from the last query if any was returned.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
