<?php

namespace App\Test;

class ClassB
{
    public function __construct(ClassC $c)
    {
        $this->c = $c;
    }
}
