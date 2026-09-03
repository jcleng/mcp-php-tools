<?php

namespace PhpMcp\Http\Prompt;

interface PromptInterface
{
    public function name(): string;

    public function description(): ?string;

    public function arguments(): array;

    public function getMessages(array $arguments = []): array;
}
