<?php

namespace App\Test;

class ClassA
{
    public function __construct(ClassB $b)
    {
        $this->b = $b;
    }
}
