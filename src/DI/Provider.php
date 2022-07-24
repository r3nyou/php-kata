<?php

namespace marcusjian\DI;

interface Provider
{
    public function get();

    public function getDependencies(): array;
}