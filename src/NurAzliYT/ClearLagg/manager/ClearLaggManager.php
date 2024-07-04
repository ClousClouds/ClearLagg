<?php

namespace NurAzliYT\ClearLagg\manager;

use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\entity\object\ItemEntity;
use NurAzliYT\ClearLagg\Main;

class ClearLaggManager {

    private $plugin;
    private $clearInterval;
    private $clearMessage;
    private $warningMessage;
    private $broadcastInterval;
    private $broadcastMessage;
    private $timeRemaining;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function init(): void {
        $config = $this->plugin->getConfig();
        $this->clearInterval = $config->get("clear-interval", 300);
        $this->clearMessage = $config->get("clear-message", "§aGarbage collected correctly.");
        $this->warningMessage = $config->get("warning-message", "§cPicking up trash in {time}...");
        $this->broadcastInterval = $config->get("broadcast-interval", 15);
        $this->broadcastMessage = $config->get("broadcast-message", "§bThe items will be deleted in {time} seconds.");
        $this->timeRemaining = $config->getNested("notify-players.countdown", 299);

        $this->plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
            $this->onTick();
        }), 20);

        $this->plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
            $this->broadcastTime();
        }), $this->broadcastInterval * 20);
    }

    private function onTick(): void {
        if ($this->timeRemaining <= 5 && $this->timeRemaining > 0) {
            Server::getInstance()->broadcastMessage(str_replace("{time}", (string)$this->timeRemaining, $this->warningMessage));
        }

        if ($this->timeRemaining <= 0) {
            $this->clearItems();
            $this->timeRemaining = $this->clearInterval;
        } else {
            $this->timeRemaining--;
        }
    }

    public function clearItems(): void {
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if ($entity instanceof ItemEntity) {
                    $entity->flagForDespawn();
                }
            }
        }
        Server::getInstance()->broadcastMessage($this->clearMessage);
    }

    private function broadcastTime(): void {
        Server::getInstance()->broadcastMessage(str_replace("{time}", (string)$this->timeRemaining, $this->broadcastMessage));
    }
}
