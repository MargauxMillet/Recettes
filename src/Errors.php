<?php

namespace App;

class Errors {

    private $errors = [];

    public function __construct ($errors)
    {
        $this->errors = $errors;
    }

    public function getErrors ($name)
    {
        if(isset($this->errors[$name])) {
            $errorName = $this->errors[$name];
            $divHTML = <<<HTML
<p class="red catamaran p-20">$errorName</p>
HTML;
            return $divHTML;
        }
    }

}

?>