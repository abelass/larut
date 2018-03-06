<?php
/**
 * Définitions pour la personnalisation du message pour le plugin
 * Message personnalisé https://github.com/abelass/message_personnalise.
 *
 * @plugin     Larut
 * @copyright  2013 - 2018
 * @author     Rainer Muller
 * @licence    GNU/GPL
 * @package    SPIP\Larut\Messages_personalises
 */

/**
 * Définitions pour la personnalisation du message reservation clôture
 *
 * @param array $args
 *        	Variables du contexte.
 * @return array Définition.
 */
function messages_personnalises_reservation_cloture_dist($args) {

	// Les champs reservations
	$reservations = lister_tables_objets_sql('spip_reservations');
	$statuts = array();
	foreach ($reservations['statut_textes_instituer'] as $statut => $chaine) {
		if (in_array($statut, array('cloture'))) {
			$statuts[$statut] = _T($chaine);
		}
	}

	// Les champs auteurs

	$articles = lister_tables_objets_sql('spip_articles');
	$tables = array(
		'articles' => array_keys($articles['field']),
	);
	$exclus = array('pass',
		'id_secteur',
		'export',
		'date_redac',
		'visites',
		'referers',
		'popularite',
		'accepter_forum',
		'date_modif',
		'surtitre',
		'soustitre',
		'id_rubrique',
		'ps',
		'langue_choisie',
		'id_trad',
		'id_rubrique',
		'nom_site',
		'url_site',
		'virtuel',
		'copyright',
		'resume',
		'texte_emai',
		'composition',
		'composition_lock',
		'action_cloture',
	);
	$champs_disponibles = array();

	foreach ($tables AS $objet => $liste_champs) {
		foreach($liste_champs AS $champ) {
			if (!in_array($champ, $exclus)) {
				$champs_disponibles[] = $champ;
			}
		}
	}


	return array(
		'label' => _T('larut:titre_message_cloture'),
		'objet' => 'article',
		'declencheurs' => array(
			'statut' => array(
				'data' => $statuts,
				'obligatoire' => 'oui',
			),
			'qui' => array(
				'data' =>
					array(
						'client' => _T('reservation:notifications_client_label'),
						'vendeur' => _T('reservation:notifications_vendeur_label'),
					),
			),
		),
		'raccoursis' => array(
			'requete' => array(
				'champs' => $champs_disponibles,
			),
			'champs' => array(
				'disponibles' => $champs_disponibles,
			),
		),
	);
}
