<?php

declare(strict_types=1);

namespace NhanAZ\CommandBlocker;

use NhanAZ\CommandBlocker\command\BlockerCommand;
use NhanAZ\CommandBlocker\listener\EventHandler;
use NhanAZ\CommandBlocker\utils\Utils;
use pocketmine\lang\Language;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase {
    use SingletonTrait;

    private Language $language;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
        $this->getServer()->getCommandMap()->register("CommandBlocker", new BlockerCommand($this, "commandblocker", "CommandBlocker command", ["cb"]));

        $defaultLanguage = "eng";
        $languageCode = $this->getConfig()->get("language", $defaultLanguage);
        if (!is_string($languageCode) || $languageCode === "") {
            $this->getLogger()->warning("Invalid language setting, using default language");
            $languageCode = $defaultLanguage;
        }
        $languagesFolder = $this->getFile() . "resources/languages/";

        if (!file_exists($languagesFolder . $languageCode . ".ini")) {
            $this->getLogger()->warning("Language $languageCode not found, using default language");
            $languageCode = $defaultLanguage;
        }

        $this->language = new Language($languageCode, $languagesFolder);
    }

    public function getLanguage(): Language {
        return $this->language;
    }

}
