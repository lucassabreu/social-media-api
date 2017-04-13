<?php

namespace Core\Util;

use DateTime as SplDateTime;

/**
 * Extension of <code>\DateTime</code> with util methods
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 *
 * @property int $year Year of date
 * @property int $month Month of date
 * @property int $day Day of date
 */
class DateTime extends SplDateTime
{
    public function getYear()
    {
        return intval($this->format('Y'));
    }

    public function getMonth()
    {
        return intval($this->format('m'));
    }

    public function getDay()
    {
        return intval($this->format('d'));
    }

    public function setYear($year)
    {
        return $this->setDate($year, $this->getMonth(), $this->getDay());
    }

    public function setMonth($month)
    {
        return $this->setDate($this->getYear(), $month, $this->getDay());
    }

    public function setDay($day)
    {
        return $this->setDate($this->getYear(), $this->getMonth(), $day);
    }

    public function __get($name)
    {
        $propertyName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        if (method_exists($this, "get$propertyName")) {
            $propertyName = "get$propertyName";
            return $this->{$propertyName}();
        } else {
            if (method_exists($this, "is$propertyName")) {
                $propertyName = "is$propertyName";
                return $this->{$propertyName}();
            } else {
                return null;
            }
        }
    }

    public function __set($name, $value)
    {
        $propertyName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        if (method_exists($this, "set$propertyName")) {
            $propertyName = "set$propertyName";
            return $this->{$propertyName}($value);
        } else {
            return null;
        }
    }

    public function __toString()
    {
        return $this->format('Y-m-d');
    }
}
