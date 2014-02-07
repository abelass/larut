<?php
if (!defined('_ECRIRE_INC_VERSION')) return; 

//Surcharge de inc_donnees_reservations_details_dist· du plugin reservation_evenements
function inc_donnees_reservations_details($id_reservations_detail,$set) {
	
    if(count($set)>0){
        include_spip('inc/filtres');
        $reservations_details=sql_fetsel('*','spip_reservations_details','id_reservations_detail='.$id_reservations_detail);

        $id_evenement=isset($set['id_evenement'])?$set['id_evenement']:$reservations_details['id_evenement'];    
     // Les données de l'évènenement

        $id_prix_objet=$set['id_prix_objet'];
        $evenement=sql_fetsel('*','spip_evenements','id_evenement='.$id_evenement);
        $date_debut=$evenement['date_debut'];
        if(!$date_fin=sql_getfetsel('date_fin','spip_evenements','id_evenement_source='.$id_evenement,'','date_debut DESC'))$date_fin=$evenement['date_fin'];

        // On établit les dates        
        if($date_debut!=$date_fin){
            if(affdate($date_debut,'d-m-Y')==affdate($date_fin,'d-m-Y')){
                $date=affdate($date_debut,'d/m/Y').','.affdate($date_debut,'G:i').'-'.affdate($date_fin,'G:i');
            }
            else {
                $date=affdate($date_debut,'d/m/Y').'-'.affdate($date_fin,'d/m/Y').', '.affdate($date_debut,'nom_jour').' '.affdate($date_debut,'G:i').'-'.affdate($date_fin,'G:i');     
                }
            }
        else{
            if(affdate($date_debut,'G:i')=='0:00')$date=affdate($date_debut,'d/m/Y');
            else $date=affdate($date_debut,'d/m/Y G:i');
        }
        // Les déscriptif
        $set['descriptif']=$evenement['titre'].' - '.$date;
        if(intval($evenement['places']))$set['places']=$evenement['places'];
        if(intval($quantite[$id_evenement]))$set['quantite']=$quantite[$id_evenement];
        else $set['quantite']=1; 
        
        // Si le prix n'est pas fournit, on essaye de le trouver
        if(!isset($set['prix']) AND !isset($set['prix_ht']) AND isset($set['evenements'])){
             $fonction_prix = charger_fonction('prix', 'inc/');
             $fonction_prix_ht = charger_fonction('ht', 'inc/prix');
             $id_rubrique=sql_getfetsel('id_rubrique','spip_evenements LEFT JOIN spip_articles USING(id_article)','id_evenement ='.$id_evenement);
                
             $sql=sql_select('id_article','spip_articles','id_rubrique='.$id_rubrique);
             
             $total_articles=sql_count($sql);
             $articles=array();
             while($data=sql_fetch($sql)){
                 $articles[]=$data['id_article'];
            	}
            $total_evenements=sql_countsel('spip_evenements','id_article IN ('.implode(',',$articles).') AND id_evenement IN ('.implode(',',$set['evenements']).')');
            
            if($total_articles==$total_evenements){
               $id_objet=sql_getfetsel('id_objet_dest','spip_selection_objets','id_objet='.$id_rubrique.' AND objet="rubrique"');
                
               $id_declinaison=sql_getfetsel('id_declinaison','spip_prix_objets ','id_prix_objet='.$id_prix_objet);
               
                $p=sql_fetsel('prix_ht,id_prix_objet,id_declinaison','spip_declinaisons LEFT JOIN spip_prix_objets USING(id_declinaison)','descriptif LIKE"'.$id_declinaison.'" AND id_objet='.$id_objet);
                
                $prix_ht = $fonction_prix_ht('prix_objet', $p['id_prix_objet']);
                $prix = $fonction_prix('prix_objet',$p['id_prix_objet']);
                $taxe = round(($prix - $prix_ht) / $prix_ht, 3);
                $set['prix_ht']=$prix_ht/$total_articles; 
                $set['taxe']=$taxe;  
                $set['id_prix_objet']=$id_prix_objet;
                }
            else{
                $p=sql_fetsel('prix_ht,id_prix_objet,id_declinaison','spip_prix_objets','id_prix_objet='.$id_prix_objet);
                $prix_ht = $fonction_prix_ht('prix_objet', $p['id_prix_objet']);
                $prix = $fonction_prix('prix_objet',$p['id_prix_objet']);
                $taxe = round(($prix - $prix_ht) / $prix_ht, 3);
                $set['prix_ht']=$prix_ht; 
                $set['taxe']=$taxe;  
                $set['id_prix_objet']=$id_prix_objet;
            	}            
            if($p['id_declinaison']>0)$set['descriptif'].=' - '.supprimer_numero(sql_getfetsel('titre','spip_declinaisons','id_declinaison='.$p['id_declinaison']));
          }
       }
    return $set;
}


// Surcharge de notifications_reservation_client_dist
function notifications_reservation_client($quoi,$id_reservation, $options) {   
    include_spip('inc/config');
    $config = lire_config('reservation_evenement');
    $envoyer_mail = charger_fonction('envoyer_mail','inc');
	$id_reservations_detail=$options['id_reservations_detail'];
	
    $options['id_reservation']=$id_reservation;  
    $options['qui']='client';     
    $subject=_T('reservation:votre_reservation_sur',array('nom'=>$GLOBALS['meta']['nom_site']));

    $message=recuperer_fond('notifications/contenu_reservation_mail',$options);
    $o=array('html'=>$message);
    // Envoyer les emails

    /*Definir le document à attacher*/
    $where=array('id_reservation='.$id_reservation);
	if($id_reservations_detail)$where[]='id_reservations_detail='.$id_reservations_detail;
	
    //Les articles concernés par la réservation
    $sql=sql_select('id_article','spip_reservations_details RIGHT JOIN spip_evenements USING (id_evenement)',$where);
    
    $id_article=array();
    
    while($data=sql_fetch($sql)) $id_article[] = $data['id_article'];

    //Les documents attachés à ces articles quin on un mot clés attaché correpondant au statut de la réservation
    $sql=sql_select('*','spip_mots LEFT JOIN spip_mots_liens USING(id_mot) LEFT JOIN spip_documents ON spip_mots_liens.id_objet=spip_documents.id_document LEFT JOIN spip_documents_liens USING (id_document) LEFT JOIN spip_types_documents USING(extension)','spip_mots.titre ='.sql_quote($options['statut']).' AND spip_documents_liens.id_objet IN ('.implode(',',$id_article).') AND spip_documents_liens.objet="article"');
    $id_document=array();
    while($doc=sql_fetch($sql)){
        $fichier=$doc['fichier'];
         $id_document[]=$doc['id_document'];
        list($extension,$nom)=explode('/',$fichier);
        $chemin=realpath(_DIR_IMG.$fichier);
        $o['pieces_jointes'][] = array('chemin' => $chemin,'nom' => $nom,'encodage' => 'base64','mime' => $doc['mime_type']) ;
        }

    $envoyer_mail($options['email'],$subject,$o);
    
        if ($archiver = charger_fonction('archiver_notification','inc',true)) {
                $envoi='reussi';
                if(!$envoyer_mail)$envoi='echec';

             $o=array(
                'recipients'=>$options['email'],                         
                'sujet'=>$subject,
                'texte'=>$message,
                'html'=>'oui',
                'id_objet'=>$id_reservation,
                'objet'=>'reservation',
                'envoi'=>$envoi,
                //'id_document'=>$id_document,
                 'type'=>$quoi);           
            
        $archiver ($o);
    } 
}
