<?php

declare(strict_types=1);

namespace UniversityMultilang\Core;

class HookManager
{
    /**
     * @var array
     */
    private array $actions = [];

    /**
     * @var array
     */
    private array $filters = [];

    public function addAction(string $hook, $component, string $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->actions[] = [
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $acceptedArgs,
        ];
    }

    public function addFilter(string $hook, $component, string $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->filters[] = [
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $acceptedArgs,
        ];
    }

    public function registerHooks(): void
    {
        foreach ($this->actions as $action) {
            add_action(
                $action['hook'],
                [$action['component'], $action['callback']],
                $action['priority'],
                $action['accepted_args']
            );
        }

        foreach ($this->filters as $filter) {
            add_filter(
                $filter['hook'],
                [$filter['component'], $filter['callback']],
                $filter['priority'],
                $filter['accepted_args']
            );
        }
    }
}
