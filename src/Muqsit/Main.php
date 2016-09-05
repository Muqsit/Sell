<?php
/*
*   _____      _ _ 
*  / ____|    | | |
* | (___   ___| | |
*  \___ \ / _ \ | |
*  ____) |  __/ | |
* |_____/ \___|_|_|
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*/
 
namespace Muqsit;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\{Command, CommandSender}; //Best PHP7 feature.
use pocketmine\utils\{TextFormat as TF, Config};
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {

  public function onEnable(){
    if(!file_exists($this->getDataFolder() . "sell.yml")){
      @mkdir($this->getDataFolder());
      file_put_contents($this->getDataFolder() . "sell.yml",$this->getResource("sell.yml"));
    }
    $this->getLogger()->info(TF::GREEN.TF::BOLD."
   _____      _ _ 
  / ____|    | | |
 | (___   ___| | |
  \___ \ / _ \ | |
  ____) |  __/ | |
 |_____/ \___|_|_|
 Loaded Sell by Muqsit.");
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->cfg = new Config($this->getDataFolder()."sell.yml", Config::YAML);
  }
  
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
    if($sender instanceof Player) {
    if(strtolower($cmd->getName()) == "sellhand") {
        if($sender->hasPermission("sell") || $sender->hasPermission("sell.hand")){
          if(count($args) > 1){
            $sender->sendMessage(TF::RED.TF::BOLD."SELL: ".TF::RESET.TF::RED."Use /sellhand");
            return false;
          }
         
            if($sender->isCreative()){
              $sender->sendMessage(TF::RED.TF::BOLD."SELL: ".TF::RESET.TF::RED."You cannot sell in creative mode.");
              return false;
            }
            $i = $sender->getInventory()->getItemInHand();
            if($i->getId() === 0){
              $sender->sendMessage(TF::RED . TF::BOLD ."SELL: ". TF::RESET . TF::RED ."You haven't equipped any item in your hand.");
              return false;
            }
            if($this->cfg->get($i->getId()) == null){
              $sender->sendMessage(TF::RED . TF::BOLD ."SELL: ". TF::RESET . TF::RED ."This item cannot be sold.");
              return false;
            }
            EconomyAPI::getInstance()->addMoney($sender, $this->cfg->get($i->getId()) * $i->getCount());
            $sender->getInventory()->removeItem($i);
            $price = $this->cfg->get($i->getId()) * $i->getCount();
            $sender->sendMessage(TF::GREEN . TF::BOLD . "SELL: " . TF::RESET . TF::GREEN . "$" . $price . " has been added to your account.");
            $sender->sendMessage(TF::GREEN . TF::BOLD . "SELL: ".TF::RESET . TF::GREEN . "Sold for " . TF::RED . "$" . $price . TF::GREEN . " (" . $i->getCount() . " " . $i->getName() . " at $" . $this->cfg->get($i->getId()) . " each).");
          
        }else{
          $sender->sendMessage(TF::RED . TF::BOLD . "SELL :".TF::RESET . TF::DARK_RED . "You don't have permission to quick sell!");
          return false;
        }
     
      }
    }
   else {
			        $this->getServer()->getLogger()->info("Command must be run ingame");}
  }
  }
