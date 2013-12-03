<?php
if (!defined('_ECRIRE_INC_VERSION')) return; 

function larut_recuperer_fond($flux){
    $fond=$flux['args']['fond'];
    if ($fond == 'formulaires/reservation'){
        $champs_amis=recuperer_fond('formulaires/inc-reservation_amies',$flux['data']);
        $login = pipeline('social_login_links', '');
        $flux['data']['texte'] = str_replace('<!--extra-->', '<!--extra-->' .  $champs_amis, $flux['data']['texte']);
    }
    
    return $flux;
}

function larut_formulaire_charger($flux){
    $form = $flux['args']['form'];
    if ($form == 'reservation'){
        $flux['data']['email_amies'] .= "";
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