<?php
/**
 * Déclarations relatives à la base de données
 *
 * @plugin     Larut
 * @copyright  2013
 * @author     Rainer Müller
 * @licence    GNU/GPL
 */

if (!defined('_ECRIRE_INC_VERSION')) return;



/**
    Nouveau statut archive pour les articles
 */
function larut_declarer_tables_objets_sql($tables) {

    $tables['spip_articles']['statut_textes_instituer']['archive']='larut:texte_statut_archive';
    $tables['spip_articles']['statut_images']=array(
            'prepa'=>'puce-preparer-8.png',
            'prop'=>'puce-proposer-8.png',
            'publie'=>'puce-publier-8.png',
            'refuse'=>'puce-refuser-8.png',
            'poubelle'=>'puce-supprimer-8.png',
            'archive'=>'puce-article-archive-8.png'
        );  

    
    $tables['spip_articles']['statut']= array(
                    array(
                        'champ' => 'statut',
                        'publie' => 'publie',
                        'previsu' => 'publie,prop,prepa,archive',
                        'post_date' => 'date',
                        'exception' => 'statut'
                    )
               );

    
    return $tables;
}

// Ajout de champs extras
function larut_declarer_champs_extras($champs = array()) {

	$champs['spip_evenements']=array(
		'lang' => array(

			'saisie' => 'selection_lang_evenement',//Type du champ (voir plugin Saisies)

			'options' => array(
					'nom' => 'lang',
					'label' => _T('reservation:label_lang'),
					'sql' => "varchar(30) NOT NULL DEFAULT ''",
					'defaut' => '',// Valeur par défaut
					'id_evenement'=>_request('id_evenement'),
					'id_article'=>_request('id_article'),					
					'restrictions'=>array('voir' => array('auteur' => ''),//Tout le monde peut voir
							'modifier' => array('auteur' => 'webmestre')),//Seuls les webmestres peuvent modifier
				),
				
			),
			
		);

	return $champs;

}
?>