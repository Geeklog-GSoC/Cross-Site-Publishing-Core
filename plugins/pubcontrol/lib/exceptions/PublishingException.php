<?php

class PublishingException extends Exception
{
    /**
     *
     * @param <type> $message
     * @param <type> $code
     * @param <type> $previous
     */
    public function __construct($message, $code=NULL, $previous=NULL)
    {
        parent::__construct($message, $code, $previous);
    }

}

?>
