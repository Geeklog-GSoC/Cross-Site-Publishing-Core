<?php

class ReceivingException extends Exception
{
    /**
     *
     * @param <type> $message
     * @param <type> $code
     * @param <type> $previous
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }

}

?>