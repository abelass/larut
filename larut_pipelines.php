<?php
if (!defined('_ECRIRE_INC_VERSION')) return; 

function larut_recuperer_fond($flux){
    $fond=$flux['args']['fond'];
	
	//Inclure les champs extras dans le formulaire reservation
    if ($fond == 'inclure/champs_listes'){
        $champs_amis=recuperer_fond('formulaires/inc-reservation_amies',$flux['data']);
        $flux['data']['texte'] .= $champs_amis;
    }
	//Selecteur des mailignlists
    /*if ($fond == 'formulaires/newsletter_subscribe'){
    	$flux['data']['status']='open';
		$contexte=$flux['data']['contexte'];
		$contexte['status']='open';
		$contexte['name']='listes';		
        $listes=recuperer_fond('formulaires/inc-check-subscribinglists',$contexte);
        $flux['data']['texte'] = str_replace('<!--extra-->',$listes. '<!--extra-->',$flux['data']['texte']);
    }-*/    
    return $flux;
}

function larut_formulaire_charger($flux){
    $form = $flux['args']['form'];
	//Formulaire reservations
    if ($form == 'reservation'){
        $flux['data']['email_amies'] = "";
    }
	
    /*if ($form == 'newsletter_subscribe'){
    	include_spip('inc/mailsubscribers');
		if (isset($GLOBALS['visiteur_session']['email']))
			$email = $GLOBALS['visiteur_session']['email'];
		elseif (isset($GLOBALS['visiteur_session']['session_email']))
			$email = $GLOBALS['visiteur_session']['session_email'];		
		If($email){
			$listes=sql_getfetsel('listes','spip_mailsubscribers','email='.sql_quote($email));
			$listes=explode(',',$listes);
			}
		$flux['data']['listes'] = $listes;
		$flux['data']['choix_listes'] = mailsubscribers_listes();
    }*/
    
    
    return $flux;
}

function larut_formulaire_verifier($flux){
    $form = $flux['args']['form'];
	//Formulaire reservations
	
    if ($form == 'newsletter_subscribe'){
    	include_spip('inc/mailsubscribers');
		
		if(function_exists('mailsubscribers_listes'))$flux['data']['choix_listes'] = mailsubscribers_listes();
    }
    
    
    return $flux;
}


function larut_formulaire_traiter($flux){
    $form = $flux['args']['form'];
    if ($form == 'reservation'){
        if($emails_amies=_request('email_amies')){
             $notifications = charger_fonction('notifications', 'inc', true);
            $options=array('from'=>_request('email'),'email'=>explode(',',$emails_amies),'evenements'=>_request('evenements'), 'nom'=>_request('nom'));
            $notifications('email_amies', $data['id_reservation'], $options);
        }

    }
    
    return $flux;
}

// Définitions des notifications pour https://github.com/abelass/notifications_archive
function larut_notifications_archive($flux){
    $flux=array_merge($flux,array(
    'email_amies'=>array(
        'activer'=>'on',
        'duree'=>'30'  
        ),      
    ));
       
    return $flux;   
}