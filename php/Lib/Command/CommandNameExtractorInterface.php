<?php

namespace Lib\Command;

interface CommandNameExtractorInterface
{
    public function extract(): string;
}