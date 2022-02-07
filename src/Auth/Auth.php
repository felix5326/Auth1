<?php

namespace Auth;

use pocketmine\plugin\PluginBase;

use pocketmine\command\{Command, CommandSender};

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger as M;

use pocketmine\Player;

use Auth\FormAPI\CustomForm;

class Auth extends PluginBase implements Listener 
{
    /** @var array */
    public $login = [];
    
    public function onEnable()
	{
                M::getLogger()->info("Auth1 plugin enabled by timmydev1");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
}
