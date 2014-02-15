<?php
/**
 * Fonctions du squelette associé
 *
 * @plugin     Réservation Événements
 * @copyright  2013
 * @author     Rainer Müller
 * @licence    GNU/GPL
 * @package    SPIP\Reservation_evenement\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

function statuts_complet(){
	$statuts_complets=charger_fonction('complet','inc/statuts');
	$statuts=$statuts_complets();
	return $statuts;
}

?>