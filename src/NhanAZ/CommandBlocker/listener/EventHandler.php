<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\listener;

use NhanAZ\CommandBlocker\blocker\WorldBlocker;
use NhanAZ\CommandBlocker\blocker\Worlds;
use NhanAZ\CommandBlocker\listener\event\CommandBlockerEvent;
use NhanAZ\CommandBlocker\utils\LanguageEnums;
use NhanAZ\CommandBlocker\utils\LanguageTrait;
use NhanAZ\CommandBlocker\utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use pocketmine\Server;

class EventHandler implements Listener {
    use LanguageTrait;

    public function onCommandEvent(CommandEvent $event): void {
        $sender = $event->getSender();
        if (!$sender instanceof Player) {
            return;
        }

        $commandParts = preg_split('/\s+/', trim($event->getCommand()));
        if ($commandParts === false || $commandParts === []) {
            return;
        }
        $world = $sender->getWorld();
        $commandName = strtolower(ltrim($commandParts[0], '/'));

        $commandMap = Server::getInstance()->getCommandMap();
        $command = $commandMap->getCommand($commandName);

        if ($command === null) {
            return;
        }

        $permission = array_values($command->getPermissions());
        $blockers = Worlds::get($world);
        $matchingBlocker = $blockers?->getCommandBlocker($command->getName())
            ?? $blockers?->getCommandBlocker($commandName)
            ?? $blockers?->testPermissions($permission);

        if (!$matchingBlocker) {
            $globalsBlocker = WorldBlocker::fromArray(Utils::getGlobals());
            $matchingBlocker = $globalsBlocker->getCommandBlocker($command->getName())
                ?? $globalsBlocker->getCommandBlocker($commandName)
                ?? $globalsBlocker->testPermissions($permission);
        }

        if ($matchingBlocker) {
            if ($matchingBlocker->hasArguments() && !$matchingBlocker->isArgumentsBlocked(array_slice($commandParts, 1))) {
                return;
            }
            $blockerEvent = new CommandBlockerEvent($sender, $matchingBlocker);
            $blockerEvent->call();
            if ($blockerEvent->isCancelled()) {
                return;
            }
            if ($matchingBlocker->hasLimit()) {
                $matchingBlocker->trigger($sender);
            }
            $event->cancel();
        }

    }

    public function onCommandBlockerEvent(CommandBlockerEvent $event): void {
        if ($event->isCancelled()) {
            return;
        }
        $blocker = $event->getCommandBlocker();
        $player = $event->getPlayer();
        $warnMessage = self::translateString(LanguageEnums::WARN_MESSAGE, [$blocker->getBlockedCommand()]);
        $player->sendMessage($warnMessage);
    }
}
