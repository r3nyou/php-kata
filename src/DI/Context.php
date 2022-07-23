<?php

namespace marcusjian\DI;

interface Context
{
    public function get(string $type): ?object;
}
