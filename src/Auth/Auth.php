<?php

namespace Auth;

use pocketmine\plugin\PluginBase;

use pocketmine\command\{Command, CommandSender};

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

use pocketmine\event\Listener;

use pocketmine\utils\Config;
use pocketmine\utils\MainLogger as M;

use pocketmine\Player;
use pocketmine\Server;

use FormAPI\CustomForm;

use pocketmine\scheduler\Task;

class Auth extends PluginBase implements Listener 
{
    /** @var array */
    public $login = [];
    public $e = array();
    
    public function onEnable()
	{
                M::getLogger()->info("Auth1 plugin enabled by rm1");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
    public function onLogin(PlayerPreLoginEvent $event){
        $player = $event->getPlayer();
        foreach($this->getServer()->getOnlinePlayers() as $player){
            if($player->getName() == $player->getName()){
                if($this->check($player)){
                    $event->setCancelled();
                    $player->kick("§l§e» §cOyuncu zaten oyunda!", false);
                    $player->sendMessage("§l§7^^ §cAz önce biri hesabına girmeye çalıştı!");
                    $player->sendMessage("Koruma devreye girdi ve girmeye çalışan kişi atıldı!");
                }
            }
        }
    }
    public function check($e){
        if(empty($this->e[$e->getName()])){
            return true;
        }else{
            return false;
        }
    }
    /**
     * @param CommandSender $player
     * @param Command $cmd
     * @param string $commandLabel
     * @param array $array
     */
    public function onCommand(CommandSender $player, Command $cmd, string $commandLabel, array $args): bool {
        if ($cmd->getName() == "sifre") {
            if ($player instanceof Player) {
                $form = new CustomForm(function (Player $player, array $data = null){
                    if ($data == null) {
                    return;
                        
                }
				if($data[1] != $data[2]){
				    $player->sendMessage("§cGirdiğin yeni ilk şifre ile yeni ikinci şifre uyuşmuyor");

					return;
				}
				$cfg = new Config($this->getDataFolder().$player->getName().".yml", Config::YAML);

				$encrypt_method = 'AES-256-CBC';
				$secret_key = '11*_33';
				$secret_iv = '22-=**_';
				$key = hash('sha256', $secret_key);
				$iv = substr(hash('sha256', $secret_iv), 0, 16);
				$encrypt = openssl_encrypt($data[1],$encrypt_method, $key, false, $iv);
				$pass = $cfg->get("Sifre");
				$decrypt = openssl_decrypt($pass,$encrypt_method, $key, false, $iv);
                if($decrypt != $data[0]){
					$player->sendMessage("§cEski şifreniz yanlış.");

					return;
				}
				$cfg->set("Sifre", $encrypt);
				$cfg->save();
				$p->sendMessage("§aBaşarıyla şifreniz değiştirildi!");

			});
			$form->setTitle("Şifre Değiştir");
			$form->addInput("Eski Şifre");
			$form->addInput("Yeni Şifre", "örn: dogthebitzer2378");
			$form->addInput("Yeni Şifre Tekrar");
			$form->sendToPlayer($player);
            }
        }
        return true;
    }
    
    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        if ($this->login[$player->getName()] != false) {
            $event->setCancelled(true);
            $player->sendMessage("§cGiriş yapmalısın:\n§7/giris");
						return;
        }
    }
    
    /** @param PlayerJoinEvent $event */
    public function onJoin(PlayerJoinEvent $event) {
        unset($this->login[$event->getPlayer()->getName()]);
		$this->getScheduler()->scheduleDelayedTask(new AuthTask($this, $event->getPlayer()), 20 * 30);
		if(file_exists($this->getDataFolder().$event->getPlayer()->getName().".yml")){
		    $this->sendLoginForm($event->getPlayer());
		}else{
			$this->sendSignForm($event->getPlayer());
		}
    }
    
    public function sendLoginForm(Player $player): void
	{
	    $form = new CustomForm(function(Player $player, array $data = null){
			if($data == null){
				$this->sendLoginForm($player);
				return;
			}
             $cfg = new Config($this->getDataFolder().$player->getName().".yml", Config::YAML);
			$pass = $cfg->get("Sifre");
			$encrypt_method = 'AES-256-CBC';
			$secret_key = '11*_33';
			$secret_iv = '22-=**_';
			$key = hash('sha256', $secret_key);
			$iv = substr(hash('sha256', $secret_iv), 0, 16);
			$decrypt = openssl_decrypt($pass,$encrypt_method, $key, false, $iv);
			if($data[0] == $decrypt){
				$player->sendMessage("§aBaşarıyla giriş yapıldı!");
				$this->login[$player->getName()] = true;
			}else{
				$player->kick("§cŞifre yanlış");
				$this->login[$player->getName()] = true;
			}
		});
		$form->setTitle("§eGiriş");
		$form->addInput("Şifreniz");
		$form->sendToPlayer($player);
	}
	public function sendSignForm(Player $player): void {
		$form = new CustomForm(function(Player $player, array $data = null) {
			if ($data == null) {
				$this->sendSignForm($player);
				return;
			}
			if($data[0] != $data[1]){
				$player->kick("§cŞifreler eşleşmiyor");
				$this->login[$player->getName()] = true;

				return;
			}
			$encrypt_method = 'AES-256-CBC';
			$secret_key = '11*_33';
			$secret_iv = '22-=**_';
			$key = hash('sha256', $secret_key);
			$iv = substr(hash('sha256', $secret_iv), 0, 16);
			$encrypt = openssl_encrypt($data[0],$encrypt_method, $key, false, $iv);
			$cfg = new Config($this->getDataFolder().$player->getName().".yml", Config::YAML);
            $cfg->set("Sifre", $encrypt);
            $cfg->save();
            $player->sendMessage("§aBaşarıyla kayıt oldun!");
			$this->login[$player->getName()] = true;

		});
		$form->setTitle("§eKayıt Ol");
		$form->addInput("Şifre", "örn: dogthebitzer");
		$form->addInput("Şifre Tekrar");
		$form->sendToPlayer($player);
	}
}
