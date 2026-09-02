<?php

namespace PhpMcp\Http\Tool;

interface ToolInterface
{
    public function name(): string;

    public function definition(): array;

    public function execute(array $args): string;
}
