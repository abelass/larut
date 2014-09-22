<?php
if (!defined("_ECRIRE_INC_VERSION")) return;


function notifications_email_amies_dist($quoi,$id_reservation, $options) {
    $envoyer_mail = charger_fonction('envoyer_mail','inc');
    
    $options['id_reservation']=$id_reservation;  
     
    $subject=_T('larut:sujet_email_amies',array('nom'=>$options['nom']));

    $message=recuperer_fond('notifications/contenu_email_amies',$options);
     
    //
    // Envoyer les emails
    //
    //
    //

    $o=array('html'=>$message,'nom_envoyeur'=>$options['nom']);
    $envoyer_mail($options['email'],$subject,$o,$options['from']);
    
    // Si présent -  l'api de notifications_archive 
    if ($archiver = charger_fonction('archiver_notification','inc',true)) {
            $envoi='reussi';
            if(!$envoyer_mail)$envoi='echec';

            $o=array(
                'recipients'=>implode(', ',$options['email']),                         
                'sujet'=>$subject,
                'texte'=>$message,
                'html'=>'oui',
                'envoi'=>$envoi,
                 'type'=>$quoi);
                     
        $archiver ($o);
    }  
}

?>