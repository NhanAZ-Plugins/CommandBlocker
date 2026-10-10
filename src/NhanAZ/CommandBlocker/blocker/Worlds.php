<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\blocker;

use NhanAZ\CommandBlocker\utils\Utils;
use pocketmine\world\World;
use WeakMap;

final class Worlds {

    /** @var WeakMap<World, WorldBlocker>|null */
    private static ?WeakMap $worlds = null;

    public static function get(World $world) :?WorldBlocker {
        self::$worlds ??= new WeakMap();
        if (isset(self::$worlds[$world])) {
            return self::$worlds[$world];
        }
        $blocker = self::load($world);
        if ($blocker !== null) {
            self::$worlds[$world] = $blocker;
        }
        return $blocker;
    }

    public static function set(World $world, WorldBlocker $blocker) : void {
        self::$worlds ??= new WeakMap();
        self::$worlds[$world] = $blocker;
    }

    public static function remove(World $world) : void {
        if (self::$worlds !== null) {
            unset(self::$worlds[$world]);
        }
    }

    public static function has(World $world) : bool {
        return self::$worlds !== null && isset(self::$worlds[$world]);
    }

    public static function load(World $world) : ?WorldBlocker {
        return Utils::parseWorldBlocker($world);
    }

}
