<?php
if (!defined('_ECRIRE_INC_VERSION'))
	return;
function larut_recuperer_fond($flux) {
	$fond = $flux['args']['fond'];

	// Inclure les champs extras dans le formulaire reservation
	if ($fond == 'inclure/champs_listes') {
		$champs_amis = recuperer_fond('formulaires/inc-reservation_amies', $flux['data']);
		$flux['data']['texte'] .= $champs_amis;
	}

	// Ajoute le message de paiement à la notification de réservation.
	if ($fond == 'inclure/commande'
			and $id_commande = $flux['data']['contexte']['id_commande']
			and $statut = sql_getfetsel('statut', 'spip_commandes', 'id_commande=' . $id_commande)
			and ($statut == 'attente')
			and $transaction = sql_fetsel('mode, id_transaction, transaction_hash, message',
						'spip_transactions',
						'id_commande=' . $id_commande,
						'',
						'date_transaction DESC'
						)) {
				$qui = $flux['data']['contexte']['qui'];
				$mode = $transaction['mode'];
				$id_transaction = $transaction['id_transaction'];
				if ($qui == 'client') {
					if ($statut == 'attente') {
						$pattern = array('|<p class="titre h4">|','|</p>|');
						$replace = array('<h3>','</h3>');
						$texte = preg_replace($pattern, $replace, bank_afficher_attente_reglement(
								$mode,
								$id_transaction,
								$transaction['transaction_hash'],
								'')
								);
					}
					else{
						$texte = '<p>' . $transaction['message'] . '</p>';
					}
				}
				else {
					$url = generer_url_ecrire('transaction', 'id_transaction=' . $id_transaction);
					$texte = '<h2>' . _T('reservation_bank:titre_paiement_vendeur') . '</h2>';
					$texte .= '<p>' . _T('reservation_bank:message_paiement_vendeur', array(
						'mode' => $mode,
						'url' => $url,
					)
							) . '</p>';
				}
				$flux['data']['texte'] .= $texte;

			}

	return $flux;
}

function larut_formulaire_charger($flux) {
	$form = $flux['args']['form'];
	// Formulaire reservations
	if ($form == 'reservation') {
		$flux['data']['email_amies'] = "";
		if (!isset($flux['data']['evenements'])or
				(isset($flux['data']['evenements']) and count($flux['data']['evenements'] == 0)) and
				$id_evenement = $flux['data']['id_evenement']) {
				if (is_array($id_evenement)) {
					$id_evenement = implode(',', $id_evenement);
				}
			$evenement = sql_fetsel('*', 'spip_evenements', 'id_evenement_source IN (' . $id_evenement . ') AND date_fin>NOW()', '', 'date_fin DESC');
			$flux['data']['evenements'][$evenement['id_evenement_source']] = $evenement;
		}
	}

	// Formulaire éditer événement
	if ($form == 'editer_evenement') {
		// Charger la valeur par défaut, hérité de l'article
		if (!$flux['data']['multilingue']) {
			list ($objet, $id_article) = explode('|', $flux['data']['parents_id'][0]);
			$flux['data']['multilingue'] = sql_getfetsel('multilingue', 'spip_articles', 'id_article=' . $id_article);
		}
	}

	// Formulaire commande cheque.
	if ($form == 'commande_cheque') {
		// Déclarer la variable téléphone
		$telephone = '';
		if ($id_auteur = session_get('id_auteur')) {
			$telephone =  sql_getfetsel('telephone', 'spip_auteurs', 'id_auteur=' .$id_auteur );
		}

		$flux['data']['telephone'] = _request('telephone') ? _request('telephone') : $telephone;
	}

	return $flux;
}

function larut_formulaire_traiter($flux) {
	$form = $flux['args']['form'];
	if ($form == 'reservation') {
		if ($emails_amies = _request('email_amies')) {
			$notifications = charger_fonction('notifications', 'inc', true);
			$options = array (
				'from' => _request('email'),
				'email' => explode(',', $emails_amies),
				'evenements' => _request('evenements'),
				'nom' => _request('nom')
			);
			$notifications('email_amies', $data['id_reservation'], $options);
		}
	}

	// Formulaire commande cheque.
	if ($form == 'commande_cheque') {
		// Le visiteur en cours
		$id_auteur = session_get('id_auteur') > 0 ? session_get('id_auteur') : 0;

		// Insérer le téléphone dans la bd
		if ($telephone = _request('telephone')) {
			sql_updateq('spip_auteurs', array ('telephone' => $telephone), 'id_auteur=' . $id_auteur);
		}
	}

	return $flux;
}

// Définitions des notifications pour https://github.com/abelass/notifications_archive
function larut_notifications_archive($flux) {
	$flux = array_merge($flux, array (
		'email_amies' => array (
			'activer' => 'on',
			'duree' => '30'
		)
	));

	return $flux;
}