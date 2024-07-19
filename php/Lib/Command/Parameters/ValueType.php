<?php

namespace Lib\Command\Parameters;

enum ValueType
{
    case INT;
    case FLOAT;
    case BOOLEAN;
    case STRING;
}