<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\blocker\command;

use InvalidArgumentException;
use pocketmine\player\Player;
use pocketmine\Server;

class CommandBlocker {

    private string $command;
    /** @var list<string>|null */
    private ?array $arguments;
    private ?Limit $limit;
    /** @var list<string> */
    private array $permissions;

    /** @var array<string, array{lastTrigger: int, VL: int}> */
    private array $triggered = [];


    /** @param list<string>|null $arguments */
    public function __construct(string $command, ?array $arguments = null, ?Limit $limit = null) {
        $this->command = $command;
        $this->arguments = $arguments;
        $this->limit = $limit;
        $commandMap = Server::getInstance()->getCommandMap()->getCommand($command);
        $this->permissions = $commandMap !== null ? array_values($commandMap->getPermissions()) : [];
    }

    public function getCommand(): string {
        return $this->command;
    }

    /** @return list<string>|null */
    public function getArguments(): ?array {
        return $this->arguments;
    }

    public function getLimit(): ?Limit {
        return $this->limit;
    }

    /** @return list<string> */
    public function getPermissions(): array {
        return $this->permissions;
    }

    /** @return list<string> */
    public function getAliases() :array {
        $commandMap = Server::getInstance()->getCommandMap()->getCommand($this->getCommand());
        return $commandMap !== null ? $commandMap->getAliases() : [];
    }

    public function hasLimit() :bool {
        return $this->limit !== null;
    }

    public function hasArguments() :bool {
        return $this->arguments !== null;
    }

    /** @param list<string> $arguments */
    public function isArgumentsBlocked(array $arguments): bool {
        if ($this->arguments === null || count($this->arguments) !== count($arguments)) {
            return false;
        }
        foreach ($this->arguments as $index => $argument) {
            if ($argument !== $arguments[$index]) {
                return false;
            }
        }
        return true;
    }

    public function getBlockedCommand() :string {
        $command = $this->getCommand();
        $arguments = $this->getArguments();
        if($arguments !== null) {
            $command .= " " . implode("|", $arguments);
        }
        return $command;
    }

    /** @param list<string> $permission */
    public function testPermission(array $permission): bool {
        $intersect = array_intersect($this->permissions, $permission);
        return !empty($intersect);
    }

    public function trigger(Player $player): void {
        $limit = $this->limit;
        if ($limit === null) {
            return;
        }
        $now = time();
        $name = strtolower($player->getName());
        $state = $this->triggered[$name] ?? ["lastTrigger" => $now, "VL" => 0];
        if ($now - $state["lastTrigger"] >= $limit->getInterval()) {
            $state = ["lastTrigger" => $now, "VL" => 0];
        }
        $state["VL"]++;
        $this->triggered[$name] = $state;
        if ($state["VL"] >= $limit->getAmount()) {
            $limit->handle($player);
        }
    }


    /** @return array<string, mixed> */
    public function toArray() :array {
        return [
            "command" => $this->command,
            "arguments" => $this->arguments ?? [],
            "limit" => $this->limit?->toArray() ?? [],
            "permissions" => $this->permissions,
        ];
    }

    /** @param array<array-key, mixed> $array */
    public static function create(string $command, array $array = []) :self {
        $arguments = $array["arguments"] ?? null;
        if ($arguments !== null && (!is_array($arguments) || !array_is_list($arguments))) {
            throw new InvalidArgumentException("Command arguments must be a list of strings");
        }
        if ($arguments !== null) {
            foreach ($arguments as $argument) {
                if (!is_string($argument)) {
                    throw new InvalidArgumentException("Command arguments must be strings");
                }
            }
        }
        $limitData = $array["limit"] ?? null;
        if ($limitData !== null && !is_array($limitData)) {
            throw new InvalidArgumentException("Command limit must be a map");
        }
        return new self(
            $command,
            $arguments,
            $limitData !== null ? Limit::fromArray($limitData) : null
        );
    }

}
