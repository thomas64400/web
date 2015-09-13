<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/

define('_PHP_CONGES', 1);
define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';
include'fonctions_install.php' ;
include ROOT_PATH .'version.php' ;

$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=FALSE;
//$DEBUG=TRUE;

//recup de la langue
$lang=(isset($_GET['lang']) ? $_GET['lang'] : ((isset($_POST['lang'])) ? $_POST['lang'] : "") ) ;

if( $DEBUG ) { 
	echo "SESSION = <br>\n"; print_r($_SESSION); echo "<br><br>\n"; 
	}


	// recup des parametres
	$action = (isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : "")) ;
	$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
	$etape = (isset($_GET['etape']) ? $_GET['etape'] : (isset($_POST['etape']) ? $_POST['etape'] : 0 )) ;

if( $DEBUG ) {
	echo "action = $action :: version = $version :: etape = $etape<br>\n";
	}

if($version == 0) {  // la version à mettre à jour dans le formulaire de index.php n'a pas été choisie : renvoit sur le formulaire
	redirect( ROOT_PATH . 'install/index.php?lang='.$lang);
	}

header_popup(' PHP_CONGES : '. _('install_maj_titre_1') );

// affichage du titre
echo "<center>\n";
echo "<br><H1><img src=\"".TEMPLATE_PATH."img/tux_config_32x32.png\" width=\"32\" height=\"32\" border=\"0\" title=\"". _('install_install_phpconges') ."\" alt=\"". _('install_install_phpconges') ."\"> ". _('install_maj_titre_2') ."</H1>\n";
echo "<br><br>\n";

// $config_php_conges_version est fourni par include ROOT_PATH .'version.php' ;
lance_maj($lang, $version, $config_php_conges_version, $etape, $DEBUG);

bottom();


/*****************************************************************************/
/*   FONCTIONS   */


// lance les differente maj depuis la $installed_version jusqu'à la version actuelle
// la $installed_version est préalablement déterminée par get_installed_version() ou renseignée par l'utilisateur
function lance_maj($lang, $installed_version, $config_php_conges_version, $etape, $DEBUG=FALSE)
{
	if( $DEBUG ) { echo " lang = $lang  ##  etape = $etape ## version = $installed_version<br>\n";}

	$PHP_SELF=$_SERVER['PHP_SELF'];

	include CONFIG_PATH .'dbconnect.php' ;

	//*** ETAPE 0
	if($etape==0) {
		//avant tout , on conseille une sauvegarde de la database !! (cf vieux index.php)
		echo "<h3>". _('install_maj_passer_de') ." <font color=\"black\">$installed_version</font> ". _('install_maj_a_version') ." <font color=\"black\">$config_php_conges_version</font> .</h3>\n";
		echo "<h3><font color=\"red\">". _('install_maj_sauvegardez') ." !!!</font></h3>\n";
		echo "<h2>....</h2>\n";
		echo "<br>\n";
		echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
		echo "<input type=\"hidden\" name=\"etape\" value=\"1\">\n";
		echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
		echo "<input type=\"submit\" value=\"". _('form_continuer') ."\">\n";
		echo "</form>\n";
		echo "<br><br>\n";
	} elseif($etape==1) { //*** ETAPE 1
		//verif si create / alter table possible !!!
		if( !test_create_table( $DEBUG) ) {
			echo "<font color=\"red\"><b>CREATE TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
			echo "<br>puis ...<br>\n";
			echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
			echo "<input type=\"hidden\" name=\"etape\"value=\"1\" >\n";
			echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
			echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
			echo "</form>\n";
		} elseif(!test_alter_table( $DEBUG) ) {
			echo "<font color=\"red\"><b>ALTER TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
			echo "<br>puis ...<br>\n";
			echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
			echo "<input type=\"hidden\" name=\"etape\"value=\"1\" >\n";
			echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
			echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
			echo "</form>\n";
		} elseif( !test_drop_table( $DEBUG) ) {
			echo "<font color=\"red\"><b>DROP TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
			echo "<br>puis ...<br>\n";
			echo "<form action=\"$PHP_SELF?lang=$lang\">\n";
			echo "<input type=\"hidden\" name=\"etape\"value=\"1\" >\n";
			echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
			echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
			echo "</form>\n";
		} else {
			if( !$DEBUG )
				echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=2&version=$installed_version&lang=$lang\">";
			else
				echo "<a href=\"$PHP_SELF?etape=2&version=$installed_version&lang=$lang\">". _('install_etape') ." 1  OK</a><br>\n";
		}
	} elseif($etape==2) {   //*** ETAPE 2
		$start_version=$installed_version ;
		//on lance l'execution (include) des scripts d'upgrade l'un après l'autre jusqu a la version voulue ($config_php_conges_version) ..
		if(($start_version=="1.5.0")||($start_version=="1.5.1")) {
			$file_upgrade='upgrade_from_v1.5.0.php';
			$new_installed_version="1.6.0";
			// execute le script php d'upgrade de la version1.5.0 (vers la suivante (1.6.0))
			echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$file_upgrade?version=$new_installed_version&lang=$lang\">";
		} elseif($start_version=="1.6.0") {
			$file_upgrade='upgrade_from_v1.6.0.php';
			$new_installed_version="1.7.0";
			// execute le script php d'upgrade de la version1.6.0 (vers la suivante (1.7.0))
			echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$file_upgrade?version=$new_installed_version&lang=$lang\">";
		} elseif($start_version=="1.7.0") {
			$file_upgrade='upgrade_from_v1.7.0.php';
			$new_installed_version="1.7.1";
			// execute le script php d'upgrade de la version1.7.0 (vers la suivante (1.7.1))
			echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$file_upgrade?version=$new_installed_version&lang=$lang\">";
		} else { 
	            if( !$DEBUG )
	                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=3&version=$new_installed_version&lang=$lang\">";
	            else
			echo "<a href=\"$PHP_SELF?etape=3&version=$new_installed_version&lang=$lang\">". _('install_etape') ." 2  OK</a><br>\n";
		}
	} elseif($etape==3) {     //*** ETAPE 3 FIN.
		// test si fichiers config.php ou config_old.php existent encore (si oui : demande de les éffacer !
		if( (test_config_file($DEBUG)) || (test_old_config_file($DEBUG)) ) {
			if(test_config_file($DEBUG)) {
				echo  _('install_le_fichier') ." <b>\"config.php\"</b> ". _('install_remove_fichier') .".<br> \n";
			}
			if(test_old_config_file($DEBUG)) {
				echo  _('install_le_fichier') ." <b>\"install/config_old.php\"</b> ". _('install_remove_fichier') .".<br> \n";
			}
			echo "<br><a href=\"$PHP_SELF?etape=5&version=$config_php_conges_version&lang=$lang\">". _('install_reload_page') ." ....</a><br>\n";
		} else {
			// mise à jour de la "installed_version" et de la langue dans la table conges_config
			$sql_update_version="UPDATE conges_config SET conf_valeur = '$config_php_conges_version' WHERE conf_nom='installed_version' ";
			$result_update_version = SQL::query($sql_update_version) ;
			$sql_update_lang="UPDATE conges_config SET conf_valeur = '$lang' WHERE conf_nom='lang' ";
			$result_update_lang = SQL::query($sql_update_lang) ;
			$comment_log = _('install_maj_titre_2')." (version $installed_version --> version $config_php_conges_version) ";
			log_action(0, "", "", $comment_log,  $DEBUG);
			// on propose la page de config ....
			echo "<br><br><h2>". _('install_ok') ." !</h2><br>\n";
			echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=../config/\">";
		}
	}
}

