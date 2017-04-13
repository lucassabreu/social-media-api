<?php

namespace Core\Controller;

/**
 * Offers some methods that help using a "one parameter query"
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
trait ProcessQueryStringTrait
{

    /**
     * Retrieves a array with the query informmed on the $queryString parameter
     * @param $queryString String with a query
     * @param $fields Fields to consider, if empty everything with be accepted
     * @return array
     */
    private function processQueryString($queryString, $fields = null, $notStrings = [])
    {
        $queryString = strtolower(trim($queryString));
        $conditions = explode(",", $queryString);

        if ($fields !== null) {
            foreach ($fields as $key => $field) {
                $fields[$key] = strtolower($field);
            }
        }

        foreach ($notStrings as $key => $field) {
            $notStrings[$key] = strtolower($field);
        }

        $params = [];
        foreach ($conditions as $condition) {
            $condition = trim($condition);
            if ($condition !== "" && strrpos($condition, ':') !== false) {
                if ($fields === null ||
                    in_array(substr($condition, 0, strrpos($condition, ':')),
                             $fields)) {
                    $field = substr($condition, 0, strrpos($condition, ':'));
                    $value = substr($condition, strrpos($condition, ':') + 1);

                    if (strrpos('*', $value) !== false || strrpos('%', $value) !== false) {
                        $params[$field] = str_replace('*', '%', $value);
                    } else {
                        if (!in_array($field, $notStrings)) {
                            $params[$field] = "%$value%";
                        } else {
                            $params[$field] = $value;
                        }
                    }
                }
            }
        }
        return $params;
    }
}
