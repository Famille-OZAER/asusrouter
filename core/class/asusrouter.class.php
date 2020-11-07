<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__  . '/../../3rdparty/TelnetClient.php';

class asusrouter extends eqLogic {
  public static function cron() {
   
   }
   




    /*     * *********************Méthodes d'instance************************* */
    
 // Fonction exécutée automatiquement avant la création de l'équipement 
    public function preInsert() {
        
    }

 // Fonction exécutée automatiquement après la création de l'équipement 
    public function postInsert() {
        
    }

 // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
    public function preUpdate() {
        
    }

 // Fonction exécutée automatiquement après la mise à jour de l'équipement 
    public function postUpdate() {
      log::add('asusrouter', 'debug', '============ Début postUpdate ==========');
      $defaultActions=array("refresh" => "Rafraichir");
      $defaultMessageActions=array("sendsms" => "Envoyer un sms");
      $defaultBinariesInfos=array();
      $defaultNumericInfos=array();
      $defaultOtherInfos=array("lastsms" => "Dernier sms reçu");
                        
      foreach ($defaultActions as $key => $value) {
         $this->createCmd($value, $key, 'action', 'other');
      }
                     
      foreach ($defaultMessageActions as $key => $value) {
         $this->createCmd($value, $key, 'action', 'message');
      }

      foreach ($defaultBinariesInfos as $key => $value) {
         $this->createCmd($value, $key, 'info', 'binary');
      }
      foreach ($defaultNumericInfos as $key => $value) {
         $this->createCmd($value, $key, 'info', 'numeric');
      }
      foreach ($defaultOtherInfos as $key => $value) {
         $this->createCmd($value, $key, 'info', 'string');
      }
    }
    public function createCmd($cmdName, $logicalID, $type, $subType)
    {
       $getDataCmd = $this->getCmd(null, $logicalID);
       if (!is_object($getDataCmd))
       {
          // Création de la commande
          $cmd = new asusrouterCmd();
          // Nom affiché
          $cmd->setName($cmdName);
          // Identifiant de la commande
          $cmd->setLogicalId($logicalID);
          // Identifiant de l'équipement
          $cmd->setEqLogic_id($this->getId());
          // Type de la commande
          $cmd->setType($type);
          $cmd->setSubType($subType);
          // Visibilité de la commande
          $cmd->setIsVisible(1);
          // Sauvegarde de la commande
          $cmd->save();
       }
    }
 // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
    public function preSave() {
        
    }

 // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
    public function postSave() {
        
    }

 // Fonction exécutée automatiquement avant la suppression de l'équipement 
    public function preRemove() {
        
    }

 // Fonction exécutée automatiquement après la suppression de l'équipement 
    public function postRemove() {
        
    }

    /*     * **********************Getteur Setteur*************************** */
}

class asusrouterCmd extends cmd {
  // Exécution d'une commande  
   public function execute($_options = array()) {
      log::add('asusrouter', 'debug', '============ Début execute ==========');
      if ($this->getType() != 'action') {
         return;
      }
      
      log::add('asusrouter', 'debug', 'Fonction execute démarrée');
      log::add('asusrouter', 'debug', 'EqLogic_Id : '.$this->getEqlogic_id());
      log::add('asusrouter', 'debug', 'Name : '.$this->getName());

      $asusrouterObj = asusrouter::byId($this->getEqlogic_id());
      $user=$asusrouterObj->getConfiguration('username');
      $ipaddress=$asusrouterObj->getConfiguration('ipaddress');
      $password=$asusrouterObj->getConfiguration('password');
      
      log::add('asusrouter', 'debug', 'user : '.$user);       
      log::add('asusrouter', 'debug', 'ipaddress : '.$ipaddress);         

      $TelnetClient = new TelnetClient($ipaddress);
      $TelnetClient->login($user, $password);
      $TelnetClient->setDefaultOptions();

      switch (strtoupper($this->getLogicalId()))
      {
         case "REFRESH":
            $this->RefreshInfos($TelnetClient);
            break;
         case "SENDSMS":
            log::add('asusrouter', 'debug', "SENDMESSAGE");
            $this->sendSms($_options['title'],$_options['message']);
            break;         
      }
      $TelnetClient->exec("exit");
      $TelnetClient->disconnect();
      log::add('asusrouter', 'debug', '============ Fin execute ==========');
   }
   private function sendSms($TelnetClient, $dest, $message)
   {

      /*echo "send message"
      /usr/sbin/modem_at.sh +CMGS=\"+33625671451\"\\rTestMessage\^Z
      echo "send message with spaces"
      /usr/sbin/modem_at.sh +CMGS=\"+33625671451\"\\rTest\ Message\\nLine\ 2\^Z*/
      $message=str_replace(" ", "\ ", $message);
      $TelnetClient->exec('/usr/sbin/modem_at.sh +CMGS=\"'.$dest.'\"\\r'.$message.'\^Z');

   }
   private function RefreshInfos($TelnetClient)
   {
      /*echo "Read unread messages"
      /usr/sbin/modem_at.sh +CMGL=\"REC\ UNREAD\"*/
      $TelnetClient->exec('/usr/sbin/modem_at.sh +CMGL=\"REC\ UNREAD\"');
   }
}


