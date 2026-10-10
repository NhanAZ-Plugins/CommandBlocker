<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\utils;

use InvalidArgumentException;
use NhanAZ\CommandBlocker\blocker\command\CommandBlocker;
use NhanAZ\CommandBlocker\blocker\command\Limit;
use NhanAZ\CommandBlocker\blocker\WorldBlocker;
use NhanAZ\CommandBlocker\blocker\Worlds;
use NhanAZ\CommandBlocker\Main;
use pocketmine\Server;
use pocketmine\world\World;

class Utils {

    public static function parseWorldBlocker(World $world): ?WorldBlocker {
        $config = Main::getInstance()->getConfig();
        $worldName = $world->getFolderName();
        $worlds = $config->get("worlds", []);

        if (!is_array($worlds) || !isset($worlds[$worldName]) || !is_array($worlds[$worldName])) {
            return null;
        }
        $commandBlockers = [];
        foreach ($worlds[$worldName] as $command => $blockerData) {
            if(is_int($command)) {
                if (!is_string($blockerData)) {
                    Main::getInstance()->getLogger()->warning("Invalid command entry in world $worldName");
                    continue;
                }
                $command = $blockerData;
                $blockerData = [];
            }
            if (!is_array($blockerData)) {
                Main::getInstance()->getLogger()->warning("Invalid command configuration in world $worldName");
                continue;
            }
            try {
                $commandBlockers[] = CommandBlocker::create($command, $blockerData);
            } catch (InvalidArgumentException $error) {
                Main::getInstance()->getLogger()->warning("Invalid blocker for $command in $worldName: " . $error->getMessage());
            }
        }

        return !empty($commandBlockers) ? WorldBlocker::fromArray($commandBlockers) : null;
    }

    public static function getWorldLoadedByName(string $name): ?World {
        $worldManager = Server::getInstance()->getWorldManager();
        if($worldManager->loadWorld($name)) {
            return $worldManager->getWorldByName($name);
        }
        return null;
    }

    /** @return list<string> */
    public static function getWorlds(): array {
        $config = Main::getInstance()->getConfig();
        $worlds = $config->get("worlds", []);
        if (!is_array($worlds)) {
            return [];
        }
        $names = [];
        foreach (array_keys($worlds) as $name) {
            if (is_string($name)) {
                $names[] = $name;
            }
        }
        return $names;
    }

    /** @return array<string, list<string>> */
    public static function getBlockersCommands(): array {
        $blockers = [];
        foreach (self::getWorlds() as $name) {
            $world = self::getWorldLoadedByName($name);
            if ($world === null) {
                continue;
            }
            $blocker = Worlds::get($world);
            if ($blocker !== null) {
                $blockers[$world->getFolderName()] = $blocker->getCommands();
            }
        }
        return $blockers;
    }


    /**
     * @return list<CommandBlocker>
     */
    public static function getGlobals(): array {
        $config = Main::getInstance()->getConfig();
        $globals = $config->get("globals", []);
        if (!is_array($globals)) {
            return [];
        }
        $blockers = [];
        foreach ($globals as $command) {
            if (is_string($command)) {
                $blockers[] = CommandBlocker::create($command);
            }
        }
        return $blockers;
    }

}
