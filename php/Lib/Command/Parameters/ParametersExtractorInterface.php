<?php

namespace Lib\Command\Parameters;

interface ParametersExtractorInterface
{
    /**
     * @param ParameterDescriptionInterface[] $parametersDescriptionList
     */
    public function extract(array $parametersDescriptionList): ParametersCollection;
}