<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\command\sub;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use NhanAZ\CommandBlocker\blocker\Worlds;
use NhanAZ\CommandBlocker\utils\LanguageEnums;
use NhanAZ\CommandBlocker\utils\LanguageTrait;
use NhanAZ\CommandBlocker\utils\Utils;
use pocketmine\command\CommandSender;

class ListSub extends BaseSubCommand {
    use LanguageTrait;


    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void {
        $this->setPermission("commandblocker.command.list");
        $this->registerArgument(0, new RawStringArgument("world"));
        $this->registerArgument(1, new RawStringArgument("command", true));
    }

    /** @param array<string, mixed> $args */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $worldName = $args["world"] ?? null;
        if (!is_string($worldName)) {
            $this->sendUsage();
            return;
        }
        $world = Utils::getWorldLoadedByName($worldName);
        if($world === null) {
            $sender->sendMessage(self::translateString(LanguageEnums::WORLD_NOT_FOUND, [$worldName]));
            return;
        }
        $blocker = Worlds::get($world);
        if ($blocker === null) {
            $sender->sendMessage(self::translateString(LanguageEnums::DONT_HAVE_BLOCKER, [$worldName]));
            return;
        }

        $command = $args["command"] ?? null;
        if (is_string($command)) {
            if($blocker->hasCommand($command)) {
                $commandBlocker = $blocker->getCommandBlocker($command);
                if($commandBlocker === null) {
                    $sender->sendMessage(self::translateString(LanguageEnums::DONT_HAVE_BLOCKER_COMMAND, [$command, $worldName]));
                    return;
                }
                $limit = $commandBlocker->getLimit();
                $amount = $limit ? $limit->getAmount() : 0;
                $time = $limit ? $limit->getInterval() : 0;

                $arguments = $commandBlocker->getArguments();
                $argsText = $arguments !== null ? implode(", ", $arguments) : "None";
                $aliases = count($commandBlocker->getAliases()) > 0 ? implode(", ", $commandBlocker->getAliases()) : "None";

                $sender->sendMessage(self::translateString(LanguageEnums::COMMAND_BLOCKED_NAME, [$command]));
                $sender->sendMessage(self::translateString(LanguageEnums::COMMAND_BLOCKED_ARGS, [$argsText]));
                $sender->sendMessage(self::translateString(LanguageEnums::COMMAND_BLOCKED_ALIAS, [$aliases]));
                $sender->sendMessage(self::translateString(LanguageEnums::COMMAND_BLOCKED_LIMIT, [$amount, $time]));
            }
        } else {
            $sender->sendMessage(self::translateString(LanguageEnums::LIST_BLOCKER_IN_WORLD, [$worldName, implode(", ", $blocker->getCommands())]));
        }
    }
}
