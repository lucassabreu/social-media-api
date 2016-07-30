<?php

namespace SocialMediaRestAPI\Controller\Exception;

class ForbiddenModifyRequestException extends \Exception {

    public function __construct ($message = NULL, $code = 0, Throwable $previous = NULL) {
        $message = $message === null ? "You can't modify others users data !" : $message;
        parent::__construct($message, $code, $previous);
    }

}