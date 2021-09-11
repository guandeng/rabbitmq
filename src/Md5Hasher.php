<?php

namespace Guandeng\Rabbitmq;

class Md5Hasher
{
    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public function make($value, array $options = [])
    {
        $salt = isset($options['salt']) ? $options['salt'] : "";
        return hash('md5', $value . $salt);
    }
    /**
     * @param $value
     * @param $hashValue
     * @param array $options
     * @return bool
     */
    public function check($value, $hashValue, array $options = [])
    {
        $salt = isset($options['salt']) ? $options['salt'] : "";
        return hash('md5', $value . $salt) === $hashValue;
    }
}
