<?php
/**
 * Fichier gérant l'installation et désinstallation du plugin Réservation Événements
 *
 * @plugin     Larut
 * @copyright  2013
 * @author     Rainer Müller
 * @licence    GNU/GPL
 * @package    SPIP\Larut\Installation
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Fonction d'installation et de mise à jour du plugin Réservation Événements.
 *
 * Vous pouvez :
 *
 * - créer la structure SQL,
 * - insérer du pre-contenu,
 * - installer des valeurs de configuration,
 * - mettre à jour la structure SQL
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @param string $version_cible
 *     Version du schéma de données dans ce plugin (déclaré dans paquet.xml)
 * @return void
**/

include_spip('inc/cextras');
include_spip('base/larut');

function larut_upgrade($nom_meta_base_version, $version_cible) {
	$maj = array();


	cextras_api_upgrade(larut_declarer_champs_extras(), $maj['create']);
	cextras_api_upgrade(larut_declarer_champs_extras(), $maj['1.1.1']);
	cextras_api_upgrade(larut_declarer_champs_extras(), $maj['1.2.0']);


	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}


/**
 * Fonction de désinstallation du plugin Réservation Événements.
 *
 * Vous devez :
 *
 * - nettoyer toutes les données ajoutées par le plugin et son utilisation
 * - supprimer les tables et les champs créés par le plugin.
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @return void
**/
function larut_vider_tables($nom_meta_base_version) {

	cextras_api_vider_tables(larut_declarer_champs_extras());
	sql_drop_table("spip_reservations");
	sql_drop_table("spip_reservations_details");

	# Nettoyer les versionnages et forums
	sql_delete("spip_versions",              sql_in("objet", array('reservation', 'reservations_detail')));
	sql_delete("spip_versions_fragments",    sql_in("objet", array('reservation', 'reservations_detail')));
	sql_delete("spip_forum",                 sql_in("objet", array('reservation', 'reservations_detail')));

	effacer_meta($nom_meta_base_version);
}

?>