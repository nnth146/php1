<?php

require_once "database/PdoDB.php";

class Validator
{
    use Rule;
    public $inputs;
    public $validates;
    public $errors;
    function __construct($inputs, $validates, $errors)
    {
        $this->inputs = $inputs;
        $this->validates = $validates;
        $this->errors = $errors;
    }
    public function setRule($field, $rules) {
        $this->validates[$field] = $rules;
    }
    public function validate()
    {
        $errors = [];
        foreach ($this->validates as $name => $rules) {
            if(!key_exists($name, $this->inputs)) {
                $this->inputs[$name] = null;
            }
            $results = $this->excuteRule($this->inputs[$name], $rules, $name);

            if(count($results) > 0) {
                $errors[$name] = $results;
            }
        }
        return $errors;
    }
    protected function excuteRule($input, $rules, $field) {
        foreach($rules as $rule) {
            $rule = explode(":", $rule);

            $method = $rule[0];

            $isError = false;

            $errors = [];

            if(count($rule) > 1) {
                $parameter = $rule[1];
                $parameters = [];
                array_push($parameters, $input, $parameter);

                $isError = call_user_func_array([$this, $method], $parameters);

                if($isError) {
                    array_push($errors, $this->errors[$field][$method]);
                    break;
                }
            }else {
                $isError = call_user_func([$this, $method], $input);

                if($isError) {
                    array_push($errors, $this->errors[$field][$method]);
                    break;
                }
            }
        }

        return $errors;
    }
}

trait Rule
{
    public function require($input)
    {
        if (empty($input)) {
            return true;
        }
        return false;
    }
    public function unique($input, $parameters)
    {
        $tableAndField = explode(".", $parameters);
        $table = $tableAndField[0];
        $field = "name";

        $pdo = new PdoDB();
        $fetchData = $pdo->select($table, "*");

        if(count($tableAndField) > 1) {
            $fieldAndValue = explode("=", $tableAndField[1]);

            $field = $fieldAndValue[0];

            if(count($fieldAndValue) > 1) {
                $oldValue = $fieldAndValue[1];
            }
        }

        foreach($fetchData as $data) {
            if($data[$field] === $input) {
                if(isset($oldValue) && $oldValue === $input) {
                    return false;
                }

                return true;
            }
        }
        return false;
    }
    public function min($input, $size)
    {
        if (is_numeric($input)) {
            return $input < $size;
        }
        if (is_string($input)) {
            return strlen($input) < $size;
        }
        if (is_array($input)) {
            return count($input) < $size;
        }
        return false;
    }
    public function max($input, $size)
    {
        if (is_numeric($input)) {
            return $input > $size;
        }
        if (is_string($input)) {
            return strlen($input) > $size;
        }
        if (is_array($input)) {
            return count($input) > $size;
        }
        return false;
    }
    public function image($files)
    {
        if(!isset($files)) {
            return false;
        }

        $imageTypes = ["image/jpg", "image/jpeg", "image/png"];
        if(!is_array($files["type"])) {
            if(!file_exists($files["tmp_name"])){
                return false;
            }

            if(!array_search($files["type"], $imageTypes)) {
                return true;
            }
            return false;
        }

        if(count($files["tmp_name"]) == 1 && !file_exists($files["tmp_name"][0])) {
            return false;
        }

        foreach($files["type"] as $type) {
            if(!array_search($type, $imageTypes)) {
                return true;
            }
        }
        return false;
    }
    public function filemax($files, $size)
    {
        if(!isset($files)) {
            return false;
        }

        if(!is_array($files["size"])) {
            if(!file_exists($files["tmp_name"])){
                return false;
            }

            return $files["size"] > $size*1024;
        }

        if(count($files) <= 0) {
            return false;
        }

        foreach($files["size"] as $fileSize) {
            if($fileSize > $size*1024) {
                return true;
            }
        }
        return false;
    }
}
