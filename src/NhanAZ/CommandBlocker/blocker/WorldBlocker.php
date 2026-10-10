<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\blocker;

use NhanAZ\CommandBlocker\blocker\command\CommandBlocker;

class WorldBlocker {

    /** @var list<CommandBlocker> */
    private array $blocker;

    /** @param list<CommandBlocker> $blocker */
    public function __construct(array $blocker) {
        $this->blocker = $blocker;
    }

    /** @return list<CommandBlocker> */
    public function getCommandBlockers(): array {
        return $this->blocker;
    }

    public function getCommandBlocker(string $command): ?CommandBlocker {
        foreach ($this->blocker as $blocker) {
            if ($blocker->getCommand() === $command) {
                return $blocker;
            }
        }
        return null;
    }

    /** @param list<string> $permissions */
    public function testPermissions(array $permissions): ?CommandBlocker {
        foreach ($this->blocker as $blocker) {
            $intersect = array_intersect($blocker->getPermissions(), $permissions);
            if (!empty($intersect)) {
                return $blocker;
            }
        }
        return null;
    }

    /** @return list<string> */
    public function getCommands() :array {
        $commands = [];
        foreach ($this->blocker as $blocker) {
            $commands[] = $blocker->getCommand();
        }
        return $commands;
    }

    public function hasCommand(string $command) :bool {
        foreach ($this->blocker as $blocker) {
            if ($blocker->getCommand() === $command) {
                return true;
            }
        }
        return false;
    }

    /** @return list<array<string, mixed>> */
    public function toArray(): array {
        $array = [];
        foreach ($this->blocker as $blocker) {
            $array[] = $blocker->toArray();
        }
        return $array;
    }

    /**
     * @param list<CommandBlocker> $commandBlockers
     */
    public static function fromArray(array $commandBlockers) : WorldBlocker {
        return new WorldBlocker($commandBlockers);
    }

}
