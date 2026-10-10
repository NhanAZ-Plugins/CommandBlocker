<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker\blocker\command;

use InvalidArgumentException;
use NhanAZ\CommandBlocker\utils\LanguageEnums;
use NhanAZ\CommandBlocker\utils\LanguageTrait;
use pocketmine\player\Player;

final class Limit {
    use LanguageTrait;

    private string $action;
    private int $amount;
    private int $interval;

    public function __construct(string $action, int $amount, int $interval) {
        $this->action = $action;
        $this->amount = $amount;
        $this->interval = $interval;
    }

    public function handle(Player $player): void {
        switch ($this->action) {
            case "kick":
                $player->kick(self::translateString(LanguageEnums::LIMIT_ACTION_KICK));
                break;
            case "ban":
                $player->getServer()->getNameBans()->addBan($player->getName(), self::translateString(LanguageEnums::LIMIT_ACTION_BAN));
                break;
            case "warn":
                $player->sendMessage(self::translateString(LanguageEnums::LIMIT_ACTION_WARN));
                break;
        }
    }

    public function getAction(): string {
        return $this->action;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getInterval(): int {
        return $this->interval;
    }

    /** @return array{action: string, amount: int, interval: int} */
    public function toArray(): array {
        return [
            "action" => $this->action,
            "amount" => $this->amount,
            "interval" => $this->interval
        ];
    }

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): Limit {
        $action = $data["action"] ?? null;
        $amount = $data["amount"] ?? null;
        $interval = $data["interval"] ?? null;
        if (!is_string($action) || !in_array($action, ["kick", "ban", "warn"], true)
            || !is_int($amount) || $amount < 1 || !is_int($interval) || $interval < 1) {
            throw new InvalidArgumentException("Limit needs a valid action, positive amount and positive interval");
        }
        return new Limit(
            $action,
            $amount,
            $interval
        );
    }
}
