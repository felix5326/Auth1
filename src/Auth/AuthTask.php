<?php

namespace Auth;

use pocketmine\{Player, Server};
use pocketmine\scheduler\Task;


class AuthTask extends Task{

	public function __construct(Auth $plugin, Player $player){
		$this->plugin = $plugin;
		$this->player = $player;
}

    public function onRun(int $currentTick){
		if(empty($this->plugin->login[$this->player->getName()]) && $this->player instanceof Player){
			$this->player->kick("§cDaha hızlı giriş yapmalısın!");
		}
	}
}
