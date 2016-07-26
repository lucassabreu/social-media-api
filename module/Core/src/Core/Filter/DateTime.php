<?php

namespace Core\Filter;

use Zend\Filter\FilterInterface;
use DateTime as SplDateTime;

class DateTime implements FilterInterface {

    private $format;

    public function __construct($options = []) {
        $this->format = isset($options['format']) ? $options['format'] : '!Y-m-d H:i:s';
    }

    public function filter($value) {
        if (!($value instanceof SplDateTime))
            return SplDateTime::createFromFormat($this->format, $value);
        else
            return $value;
    }

}