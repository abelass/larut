<?php
if (!defined('_ECRIRE_INC_VERSION')) return; 

function larut_recuperer_fond($flux){
    $fond=$flux['args']['fond'];
	
	//Inclure les champs extras dans le formulaire reservation
    if ($fond == 'inclure/champs_listes'){
        $champs_amis=recuperer_fond('formulaires/inc-reservation_amies',$flux['data']);
        $flux['data']['texte'] .= $champs_amis;
    }
 
    return $flux;
}

function larut_formulaire_charger($flux){
    $form = $flux['args']['form'];
	//Formulaire reservations
    if ($form == 'reservation'){
        $flux['data']['email_amies'] = "";
    }
	
    if ($form == 'editer_evenement'){
    	//Charger la valeur par dégfaut, hérité de l'article	
		if(!$flux['data']['multilingue']){
			list($objet,$id_article)=explode('|',$flux['data']['parents_id'][0]);
			$flux['data']['multilingue']=sql_getfetsel('multilingue','spip_articles','id_article='.$id_article);
		}
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