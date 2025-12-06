<?php

namespace ClousClouds\ClearLagg;

use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;

class ScoreHudListener implements Listener{

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onTagResolve(TagsResolveEvent $event) : void{
        $tag = $event->getTag();

        $clearLaggManager = $this->plugin->getClearLaggManager();
        $statsManager = $this->plugin->getStatsManager();

        switch($tag->getName()){
            case "clearlagg.total_items_cleared":
                $itemsCleared = $statsManager->getTotalItemsCleared();
                $tag->setValue((string) $itemsCleared);
                break;

            case "clearlagg.next_clear":
                $timeRemaining = $this->plugin->getTimeRemaining();
                $tag->setValue((string) $timeRemaining);
                break;
                
            case "clearlagg.items_count":
                $itemCount = 0;
                foreach($this->plugin->getServer()->getWorldManager()->getWorlds() as $world){
                    foreach($world->getEntities() as $entity){
                        if($entity instanceof \pocketmine\entity\object\ItemEntity){
                            $itemCount++;
                        }
                    }
                }
                $tag->setValue((string) $itemCount);
                break;
        }
    }
}