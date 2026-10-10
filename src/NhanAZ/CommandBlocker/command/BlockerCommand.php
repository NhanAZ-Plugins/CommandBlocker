<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\command;

use CortexPE\Commando\BaseCommand;
use NhanAZ\CommandBlocker\command\sub\ListSub;
use NhanAZ\CommandBlocker\command\sub\WorldsSub;
use pocketmine\command\CommandSender;

class BlockerCommand extends BaseCommand {

    private string $permission = "commandblocker.command";

    protected function prepare(): void {
        $this->setPermission($this->permission);
        $this->registerSubCommand(new ListSub("list", "List all command blocker"));
        $this->registerSubCommand(new WorldsSub("worlds", "List all worlds"));
    }

    /** @param array<string, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $this->sendUsage();
    }

    public function getPermission() : string{
        return $this->permission;
    }
}
