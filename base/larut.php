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

    $tables['spip_articles']['statut_textes_instituer']['archive']='larut:archive';
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
?>