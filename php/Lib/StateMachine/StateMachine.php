<?php

namespace Lib\StateMachine;

class StateMachine
{
    /**
     * @var array<string, StateInterface>
     */
    private array $stateList = [];

    /**
     * @param array<int|string, StateInterface> $stateList
     */
    public function __construct(array $stateList)
    {
        foreach ($stateList as $nameFromArray => $state) {
            $this->stateList[is_integer($nameFromArray) ? $state::getName() : $nameFromArray] = $state;
        }
    }

    public function runFromState(string $stateName, ?ContextInterface $context = null)
    {
        $nextStateName = $stateName;

        while ($nextStateName !== NULL) {

            $state = $this->stateList[$nextStateName] ?? throw new \Exception("State '$nextStateName' does not exist");

            $nextStateName = $state->run($context);
        }
    }
}