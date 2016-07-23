<?php

namespace Core\Model\Entity;

use Core\Model\DAO\DAOInterface;
use Core\Model\DAO\Exception\DAOException;
use InvalidArgumentException;
use Serializable;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\JsonSerializable;
use Zend\InputFilter\Factory;

/**
 * Base class for entities managed by Core\Model\DAOInterface.
 * 
 * @see DAOInterface
 * 
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * 
 */
abstract class Entity implements JsonSerializable, Serializable, InputFilterAwareInterface {

    /**
     * Filters
     * 
     * @var InputFilter
     */
    protected $inputFilter = null;

    /**
     * Set all entity data based in an array with data
     *
     * @param array $data
     * @return void
     */
    public abstract function setData($data);

    /**
     * Return all entity data in array format
     *
     * @return array
     */
    public abstract function getData();

    /**
     * Used by TableGateway
     *
     * @param array $data
     * @return void
     */
    public function exchangeArray($data) {
        $this->setData($data);
    }

    /**
     * Used by TableGateway
     *
     * @param array $data
     * @return void
     */
    public function getArrayCopy() {
        return $this->getData();
    }

    /**
     * @param InputFilterInterface $inputFilter
     * @return void
     */
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new DAOException("Not used");
    }

    /**
     * Entity filters
     *
     * @return InputFilter
     */
    public function getInputFilter() {
        if ($this->inputFilter === null) {
            $factory = new Factory();
            $this->inputFilter = $factory->createInputFilter(array());
        }

        return $this->inputFilter;
    }

    /**
     * Filter and validate data
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function valid($key, $value) {
        if (!$this->getInputFilter())
            return $value;

        if (!$this->getInputFilter()->has($key))
            return $value;

        try {
            $filter = $this->getInputFilter()->get($key);
        } catch (InvalidArgumentException $e) {
            //não existe filtro para esse campo
            return $value;
        }

        $filter->setValue($value);

        if (!$filter->isValid()) {
            $errors = implode(', ', $filter->getMessages());
            throw new DAOException("Invalid input: $key = '$value'. $errors");
        }

        return $filter->getValue($key);
    }

    /**
     * Used by TableGateway
     *
     * @return array
     */
    public function toArray() {
        return $this->getData();
    }

    public function serialize() {
        return serialize($this->toArray());
    }

    public function unserialize($serialized) {
        $this->setData(unserialize($serialized));
        return $this;
    }

    public function jsonSerialize () {
        return $this->toArray();
    }

    public function __toString() {
        return __CLASS__;
    }

    /**
     * Execute validations on entity
     * @return boolean
     */
    public function validate() {

        $data = get_object_vars($this);

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return true;
    }

    /**
     * Calls <code>validate</code> method on this object
     * @see Entity#validate
     * @return boolean
     */
    public function isValid() {
        return $this->validate();
    }

}

?>
