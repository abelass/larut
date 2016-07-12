<?php
if (!defined('_ECRIRE_INC_VERSION'))
  return;

// Surcharge de notifications_reservation_client_dist
function notifications_reservation_client($quoi, $id_reservation, $options) {
  include_spip('inc/config');
  $config = lire_config('reservation_evenement');
  $envoyer_mail = charger_fonction('envoyer_mail', 'inc');
  $id_reservations_detail = $options['id_reservations_detail'];

  $options['id_reservation'] = $id_reservation;
  $options['qui'] = 'client';
  $subject = _T('reservation:votre_reservation_sur', array('nom' => $GLOBALS['meta']['nom_site']));

  /*Chercher des chaines de langues spécifiques pour les différents statuts*/
  $lang = $options['lang'];

  $var_reservation = 'i18n_reservation_' . $lang;
  $chaine_statut = 'sujet_votre_reservation_' . $options['statut'];

  if (isset($GLOBALS[$var_reservation][$chaine_statut]))
    $subject = _T('reservation:' . $chaine_statut, array('nom' => $GLOBALS['meta']['nom_site']));

  $message = recuperer_fond('notifications/contenu_reservation_mail', $options);
  $o = array('html' => $message);
  // Envoyer les emails

  /*Definir le document à attacher*/
  $where = array('id_reservation=' . $id_reservation);
  if ($id_reservations_detail)
    $where[] = 'id_reservations_detail=' . $id_reservations_detail;

  //Les articles concernés par la réservation
  $sql = sql_select('id_article', 'spip_reservations_details RIGHT JOIN spip_evenements USING (id_evenement)', $where);

  $id_article = array();

  while ($data = sql_fetch($sql))
    $id_article[] = $data['id_article'];

  //Les documents attachés à ces articles qui ont un mot clés attaché correpondant au statut de la réservation
  if (count($id_article) > 0) {
    $sql = sql_select('*', 'spip_mots LEFT JOIN spip_mots_liens USING(id_mot)
			 LEFT JOIN spip_documents ON spip_mots_liens.id_objet=spip_documents.id_document
			 LEFT JOIN spip_documents_liens USING (id_document)
			 LEFT JOIN spip_types_documents USING(extension)', 'spip_mots.titre =' . sql_quote($options['statut']) . '
    		AND spip_documents_liens.id_objet IN (' . implode(',', $id_article) . ')
    		AND spip_documents_liens.objet="article"');
    $id_document = array();
    while ($doc = sql_fetch($sql)) {
      $fichier = $doc['fichier'];
      $id_document[] = $doc['id_document'];
      list($extension, $nom) = explode('/', $fichier);
      $chemin = realpath(_DIR_IMG . $fichier);
      $o['pieces_jointes'][] = array(
        'chemin' => $chemin,
        'nom' => $nom,
        'encodage' => 'base64',
        'mime' => $doc['mime_type']
      );
    }
  }
	spip_log($subject,'teste');
  spip_log($o,'teste');
  $envoyer_mail($options['email'], $subject, $o);

  if ($archiver = charger_fonction('archiver_notification', 'inc', true)) {
    $envoi = 'reussi';
    if (!$envoyer_mail)
      $envoi = 'echec';

    $o = array(
      'recipients' => $options['email'],
      'sujet' => $subject,
      'texte' => $message,
      'html' => 'oui',
      'id_objet' => $id_reservation,
      'objet' => 'reservation',
      'envoi' => $envoi,
      //'id_document'=>$id_document,
      'type' => $quoi
    );

    $archiver($o);
  }
}

//retourne les statuts qui définissent si un événement est complet
if (!function_exists('statuts_complet')) {
  function statuts_complet() {
    $statuts_complets = charger_fonction('complet', 'inc/statuts');
    $statuts = $statuts_complets();
    return $statuts;
  }

}

/**
 * Retourne les langues de l'article
 * @param string $id_article
 * @param mixed $separateur Ce qui entoure le résultat
 **/

function article_langues($id_article, $separateur = '') {
  if (is_array($separateur)) {
    list($sep, $sep_fin) = $separateur;
    $sep = $separateur[0];
    $sep_fin = $separateur[1];
  }
  else
    $sep = $separateur;

  if (!$sql = sql_select('lang', 'spip_articles', 'id_trad!=0 AND id_trad=' . $id_article))
    $sql = sql_select('lang', 'spip_articles', 'id_trad!=0 AND id_article=' . $id_article);

  $langues = '';
  while ($data = sql_fetch($sql)) {
    $langues = concat($langues, $sep . $data['lang'] . $sep_fin);
  }

  return $langues;
}
