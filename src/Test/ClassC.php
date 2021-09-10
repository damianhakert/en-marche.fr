<?php

namespace App\Test;

class ClassC
{
    public function __construct(ClassD $d)
    {
        $this->d = $d;
    }
}
