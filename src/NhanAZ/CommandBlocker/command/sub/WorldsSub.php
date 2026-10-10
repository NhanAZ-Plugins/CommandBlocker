<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\command\sub;

use CortexPE\Commando\BaseSubCommand;
use NhanAZ\CommandBlocker\utils\LanguageEnums;
use NhanAZ\CommandBlocker\utils\LanguageTrait;
use NhanAZ\CommandBlocker\utils\Utils;
use pocketmine\command\CommandSender;

class WorldsSub extends BaseSubCommand {
    use LanguageTrait;

    protected function prepare(): void {
        $this->setPermission("commandblocker.command.worlds");
    }

    /** @param array<string, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $commandBlockers = Utils::getBlockersCommands();
        foreach($commandBlockers as $world => $blockers) {
            $sender->sendMessage(self::translateString(LanguageEnums::LIST_BLOCKER_IN_WORLD, [$world, implode(", ", $blockers)]));
        }
        $sender->sendMessage(self::translateString(LanguageEnums::LIST_HELP));
    }
}
