<?php

namespace Lib\Command\Parameters;

enum ValuePresenceMod
{
    case REQUIRED;
    case OPTIONAL;
    case NO_VALUE;
    case FROM_REST;
}