<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\utils;

use NhanAZ\CommandBlocker\Main;
use pocketmine\lang\Translatable;

trait LanguageTrait {

    /** @param list<float|int|string|Translatable> $args */
    public static function translateString(string $enums, array $args = []): string {
        return Main::getInstance()->getLanguage()->translateString($enums, $args);
    }
}
