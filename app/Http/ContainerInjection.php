<?php

namespace App\Http;

use ReflectionClass;

class ContainerInjection
{
    public function make(string $class)
    {
        $reflection = new ReflectionClass($class);
        $construct  = $reflection->getConstructor();
        
        if (!$construct){
            return new $class();
        }

        $dependencies = [];

        foreach($construct->getParameters() as $param){
            $type = $param->getType();
            if ($type && !$type->isBuiltin()){
                $dependencies[] = $this->make($type->getName());
            }
        }

        return $reflection->newInstanceArgs($dependencies); 
        
    }
}