<?php

namespace KnosTx\ClearLagg\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use KnosTx\ClearLagg\Main;
use KnosTx\ClearLagg\command\subcommands\StatsCommand;

class ClearLaggCommand extends Command implements PluginOwned {
    use PluginOwnedTrait;

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("clearlagg", "Clear lag by removing items", "/clearlagg [stats]", ["cl"]);
        $this->plugin = $plugin;
        $this->setPermission("clearlagg.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            $sender->sendMessage("You don't have permission to use this command.");
            return false;
        }

        try {
            if (isset($args[0]) && $args[0] === "stats") {
                $statsCommand = new StatsCommand($this->plugin);
                return $statsCommand->execute($sender);
            } else {
                $this->plugin->getClearLaggManager()->clearItems();
                $sender->sendMessage("Items cleared successfully.");
            }
        } catch (\Exception $e) {
            $sender->sendMessage("An error occurred: " . $e->getMessage());
            $this->plugin->getLogger()->error("Error in ClearLaggCommand: " . $e->getMessage(), $e);
            return false;
        }

        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
