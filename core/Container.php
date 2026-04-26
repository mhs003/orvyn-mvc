<?php

namespace Core;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $key, callable $resolver)
    {
        $this->bindings[$key] = $resolver;
    }

    public function singleton(string $key, callable $resolver)
    {
        $this->bindings[$key] = function ($c) use ($resolver, $key) {
            if (!isset($this->instances[$key])) {
                $this->instances[$key] = $resolver($c);
            }
            return $this->instances[$key];
        };
    }

    public function instance(string $key, $object)
    {
        $this->instances[$key] = $object;
    }

    public function make(string $key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        if (isset($this->bindings[$key])) {
            return $this->bindings[$key]($this);
        }

        if (!class_exists($key)) {
            throw new \Exception("Cannot resolve {$key}");
        }

        $ref = new \ReflectionClass($key);

        $constructor = $ref->getConstructor();

        if (!$constructor) {
            return new $key;
        }

        $deps = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType()?->getName();

            if (!$type) {
                throw new \Exception("Unresolvable dependency");
            }

            $deps[] = $this->make($type);
        }

        return $ref->newInstanceArgs($deps);
    }
}