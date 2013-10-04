<?php
/**
 * Plugin Maj
 *
 * @package	PLX
 * @version	1.5
 * @date	07/08/2012
 * @author	Cyril MAGUIRE
 **/
class maj extends plxPlugin {

	public $majList = array(); # Tableau des données concernant les articles mis à jour
	public $feed = false; # Variable indiquant si l'on est dans un flux rss

	/**
	 * Constructeur de la classe maj
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# Gestion des droits d'accès à la config
		$this->setConfigProfil(PROFIL_ADMIN);
		
		# Déclarations des hooks
		$this->addHook('AdminArticlePrepend', 'AdminArticlePrepend');
		$this->addHook('AdminArticlePreview', 'AdminArticlePreview');
		$this->addHook('AdminArticlePostData', 'AdminArticlePostData');
		$this->addHook('AdminArticleParseData', 'AdminArticleParseData');
		$this->addHook('AdminArticleInitData', 'AdminArticleInitData');
		$this->addHook('AdminArticleSidebar', 'AdminArticleSidebar');
		$this->addHook('AdminArticleFoot', 'AdminArticleFoot');

		$this->addHook('AdminIndexPrepend', 'AdminIndexPrepend');

		$this->addHook('plxAdminEditArticle', 'plxAdminEditArticle');

		$this->addHook('plxMotorParseArticle', 'plxMotorParseArticle');

		$this->addHook('plxFeedDemarrageBegin', 'plxFeedDemarrageBegin');

		#Hooks du plugin
		$this->addHook('artPubliDate', 'artPubliDate');
		$this->addHook('artDateMaj', 'artDateMaj');
		$this->addHook('loopOnMaj', 'loopOnMaj');

		# Récupération des données de mise à jour des articles
		$this->getMaj(PLX_ROOT.$this->getParam('maj'));
	}

	/**
	 * Méthode qui préconfigure le plugin
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function onActivate() {
		#Paramètres par défaut
		if(!is_file($this->plug['parameters.xml'])) {
			$this->setParam('maj', 'data/articles/0000.maj.xml', 'cdata');
			$this->setParam('automaj', 'on', 'cdata');
			$this->setParam('replace', 'on', 'cdata');
			$this->saveParams();
		}
	}

	/**
	 * Méthode qui récupère les infos enregistrées dans le fichier data/articles/0000.maj.xml
	 * 
	 * @param $filename ressource le chemin vers le fichier des adhérents
	 * @return array La liste de tous les articles ayant bénéficié d'une mise à jour
	 * 
	 * @author Cyril MAGUIRE
	 */
	public function getMaj($filename) {
		
		if(!is_file($filename)) return;
		
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		if(isset($iTags['article']) AND isset($iTags['maj'])) {
			$nb = sizeof($iTags['maj']);
			$size=ceil(sizeof($iTags['article'])/$nb);
			for($i=0;$i<$nb;$i++) {
				$attributes = $values[$iTags['article'][$i*$size]]['attributes'];
				$number = $attributes['number'];
				# Recuperation de la date
				$this->majList[$number]['maj']=plxUtils::getValue($values[$iTags['maj'][$i]]['value']);
			}
		}
		foreach ($this->majList as $key => $value) {
			if (empty($key)) {
				unset($this->majList[$key]);
			}
		}
		return $this->majList;
	}

	/**
	 * Méthode qui ajoute le champs 'mise à jour' lors de l'édition de l'article
	 *
	 * @return	string Le formulaire de date de mise à jour
	 * @author	Cyril MAGUIRE
	 **/
	public function AdminArticleSidebar(){
			echo '
				<p><label>'.$this->getLang('L_ARTICLE_DATE_MAJ').'&nbsp;:</label></p>
				<p>
				<?php if ($plxAdmin->plxPlugins->aPlugins["maj"]->getParam(\'automaj\') != \'on\') :?>

				<a href="javascript:void(0)" onclick="delMaj(); return false;"><img src="<?php echo PLX_PLUGINS;?>maj/img/date_erase.png" alt="X"  title="<?php echo $plxAdmin->plxPlugins->aPlugins["maj"]->getLang(\'L_DEL\'); ?>"/></a>
				<?php endif;?>

				<?php plxUtils::printInput(\'day_maj\',$maj[\'day\'],\'text\',\'2-2\',false,\'fld1\'); ?>
				<?php plxUtils::printInput(\'month_maj\',$maj[\'month\'],\'text\',\'2-2\',false,\'fld1\'); ?>
				<?php plxUtils::printInput(\'year_maj\',$maj[\'year\'],\'text\',\'2-4\',false,\'fld2\'); ?>
				<?php plxUtils::printInput(\'time_maj\',$maj[\'time\'],\'text\',\'2-5\',false,\'fld2\'); ?>
				<a href="javascript:void(0)" onclick="majNow(<?php echo date(\'Z\') ?>); return false;"><img src="theme/images/date.png" alt="<?php echo L_NOW; ?>"  title="<?php echo L_NOW; ?>"/></a></p>';

        }
    /**
    * Méthode qui ajoute un fichier javascript en bas de page d'édition des articles
    * 
    * @return string
    * @author Cyril MAGUIRE
    */	
    public function AdminArticleFoot() {
    	echo '<script type="text/javascript" src="'.PLX_PLUGINS.'maj/majNow.js"></script>';
    }

    /**
    * Méthode qui modifie la date de création par la date de mise à jour (si nécessaire) lors de la prévisualisation d'un article
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function AdminArticlePreview() {
    	echo 
    	'<?php if (isset($_POST[\'day_maj\']) && !empty($_POST[\'day_maj\']) && !empty($_POST[\'month_maj\']) && !empty($_POST[\'year_maj\']) && !empty($_POST[\'time_maj\']) ) {
    	    		$art[\'date\'] = $_POST[\'year_maj\'].$_POST[\'month_maj\'].$_POST[\'day_maj\'].substr(str_replace(\':\',\'\',$_POST[\'time_maj\']),0,4);
    	}?>';
    }

    /**
    * Méthode qui supprime un article et sa mise à jour depuis le formulaire d'édition des articles
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function AdminArticlePrepend() {
    	echo '<?php 
    	if(!empty($_POST)) {
    		# Suppression d\'un article
			if(isset($_POST[\'delete\'])) {
				$plxAdmin->delArticle($_POST[\'artId\']);
				if ($_SESSION[\'info\'] == L_ARTICLE_DELETE_SUCCESSFUL) {
					$plxAdmin->plxPlugins->aPlugins["maj"]->delMaj($_POST[\'artId\']);
				}
				header(\'Location: index.php\');
				return true;
			}
    	}
    	?>';
    }

    /**
    * Méthode qui supprime une mise à jour
    * 
    * @param string id de l'article de la forme XXXX où les X sont à remplacer par des chiffres
    * @return bool
    * @author Cyril MAGUIRE
    */		
    public function delMaj($id) {
    	# Suppression des données de mise à jour de l'article
    	unset($this->majList[$id]);

    	# Génération du fichier XML
			$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
			$xml .= "<document>\n";
		foreach ($this->majList as $article => $date) {
			$xml .= "\t<article number=\"".plxUtils::cdataCheck(trim($article))."\">\n\t\t";
			$xml .= "<maj>".$date['maj']."</maj>\n\t";
			$xml .= "</article>\n";
		}
		$xml .= "</document>";

		# On va mettre à jour notre fichier
		if(plxUtils::write($xml,PLX_ROOT.$this->getParam('maj'))) {
			return true;
		} else {
			return false;
		}
    }

    /**
    * Méthode qui supprime des articles et leur mise à jour depuis le tableau récapitulatif des articles
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function AdminIndexPrepend() {
    	echo '<?php
    	# Suppression des articles selectionnes
		if(isset($_POST[\'selection\']) AND ($_POST[\'selection\'][0] == \'delete\' OR $_POST[\'selection\'][1] == \'delete\') AND isset($_POST[\'idArt\'])) {
			foreach ($_POST[\'idArt\'] as $k => $v) $plxAdmin->delArticle($v);
			foreach ($_POST[\'idArt\'] as $k => $v) $plxAdmin->plxPlugins->aPlugins["maj"]->delMaj($v);
			header(\'Location: index.php\');
			exit;
		}
		?>';
    }

    /**
    * Méthode qui édite le fichier de mise à jour
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function plxAdminEditArticle() {

    	echo '<?php 
    		if($content[\'artId\'] != \'0000\' && $content[\'artId\'] != \'\'):
    			//Si ce n\'est pas une création, ni un brouillon on enregistre la date de mise à jour
				if (!in_array("draft",$content["catId"])) :
					$plxPlugin = $this->plxPlugins->aPlugins["maj"];

					if ($plxPlugin->getParam(\'automaj\') == \'on\') {
						$maj[\'day\'] = date(\'d\');
						$maj[\'month\'] = date(\'m\');
						$maj[\'year\'] = date(\'Y\');
						$maj[\'hour\'] = date(\'H\');
						$maj[\'minutes\'] = date(\'i\');
						$maj[\'time\'] = $maj[\'hour\'].\':\'.$maj[\'minutes\'];
					} else {
						$maj[\'day\'] = $content[\'day_maj\'];
						$maj[\'month\'] = $content[\'month_maj\'];
						$maj[\'year\'] = $content[\'year_maj\'];
						if (!empty($content[\'time_maj\']) && $content[\'time_maj\'] != \':\') {
							$maj[\'hour\'] = substr($content[\'time_maj\'],0,2);
							$maj[\'minutes\'] = substr($content[\'time_maj\'],-2);
							$maj[\'time\'] = $maj[\'hour\'].\':\'.$maj[\'minutes\'];
						} else {
							$maj[\'time\'] = \'\';
						}			
					}
					//Si on a des informations de mise à jour, on enregistre
					if (!empty($maj[\'day\']) && !empty($maj[\'month\']) && !empty($maj[\'year\']) && !empty($maj[\'hour\']) && !empty($maj[\'minutes\'])) {
						$plxPlugin->majList[$content[\'artId\']][\'maj\'] = $maj[\'year\'].$maj[\'month\'].$maj[\'day\'].substr(str_replace(\':\',\'\',$maj[\'time\']),0,4);
						
						//echo \'<pre>\'; print_r($plxPlugin->majList);echo \'</pre>\';exit();
						
						# Génération du fichier XML
						$xml = "<?xml version=\'1.0\' encoding=\'".PLX_CHARSET."\'?>\n";
						$xml .= "<document>\n";
						
						foreach ($plxPlugin->majList as $article => $date) {
							$xml .= "\t<article number=\"".plxUtils::cdataCheck(trim($article))."\">\n\t\t";
							$xml .= "<maj>".$date[\'maj\']."</maj>\n\t";
							$xml .= "</article>\n";
						}
						$xml .= "</document>";
						
						# On va mettre à jour notre fichier
						plxUtils::write($xml,PLX_ROOT.$plxPlugin->getParam(\'maj\'));
						unset($xml);
					} else {// Sinon, on supprime l\'article des mises à jour
						$plxPlugin->delMaj($content[\'artId\']);
					}	
				endif;
			endif;
		?>';
    }

    /**
    * Méthode qui alimente les variables lors d'une création d'article
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function AdminArticlePostData() {
    	echo '<?php
    	if ($plxAdmin->plxPlugins->aPlugins["maj"]->getParam(\'automaj\') == \'on\') {
    		$maj[\'day\'] = (empty($_POST[\'day_maj\'])) ? date(\'d\') : $_POST[\'day_maj\'];
			$maj[\'month\'] = (empty($_POST[\'month_maj\'])) ? date(\'m\') : $_POST[\'month_maj\'];
			$maj[\'year\'] = (empty($_POST[\'year_maj\'])) ? date(\'Y\') : $_POST[\'year_maj\'];
			$maj[\'hour\'] = (empty($_POST[\'hour_maj\'])) ? date(\'H\') : $_POST[\'hour_maj\'];
			$maj[\'minutes\'] = (empty($_POST[\'minutes_maj\'])) ? date(\'i\') : $_POST[\'minutes_maj\'];
			$maj[\'time\'] = (empty($_POST[\'time_maj\'])) ? date(\'H:i\') : $_POST[\'time_maj\'];
    	} else {
    		$maj[\'day\'] = (empty($_POST[\'day_maj\'])) ? \'\' : $_POST[\'day_maj\'];
			$maj[\'month\'] = (empty($_POST[\'month_maj\'])) ? \'\' : $_POST[\'month_maj\'];
			$maj[\'year\'] = (empty($_POST[\'year_maj\'])) ? \'\' : $_POST[\'year_maj\'];
			$maj[\'hour\'] = (empty($_POST[\'hour_maj\'])) ? \'\' : $_POST[\'hour_maj\'];
			$maj[\'minutes\'] = (empty($_POST[\'minutes_maj\'])) ? \'\' : $_POST[\'minutes_maj\'];
			$maj[\'time\'] = (empty($_POST[\'time_maj\'])) ? \'\' : $_POST[\'time_maj\'];
    	}
	    	
		?>';
    }

    /**
    * Méthode qui alimente les variables lors de l'édition d'un article
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function AdminArticleParseData() {
    	echo '<?php
    		$dateMaj = $plxAdmin->plxPlugins->aPlugins["maj"]->majList[$artId][\'maj\'];
    		$maj = plxDate::date2Array($dateMaj);
		?>';
    }

    /**
    * Méthode qui initialise les variables lors de la création d'un article
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function AdminArticleInitData() {
    	echo '<?php
    		$maj[\'day\'] = "";
			$maj[\'month\'] = "";
			$maj[\'year\'] = "";
			$maj[\'hour\'] = "";
			$maj[\'minutes\'] = "";
			$maj[\'time\'] = "";
    	?>';
    }

    /**
    * Méthode qui remplace la date de création par la date de mise à jour si nécessaire
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function plxMotorParseArticle() {
    	echo '<?php 
    	$plxPlugin = $this->plxPlugins->aPlugins["maj"];
    	$maj = $plxPlugin->majList[$art["numero"]]["maj"];

    	//On ne modifie la date de publication par la date de mise à jour que dans la partie publique
    	//Ainsi on a toujours les dates originelles dans la partie d\'administration
    	if ($maj !== null && ($this->mode == \'article\' || $this->mode == \'home\' || $this->mode == \'categorie\' || $plxPlugin->feed == true) ){
    		if ($this->plxPlugins->aPlugins["maj"]->getParam(\'replace\') == \'on\') {
    			$art["date"] = $maj;
    			$art["publi"] = $tmp["artDate"];
    			$art["maj"] = $maj;
    		} else {
    			$art["maj"] = $maj;
    			$art["publi"] = $tmp["artDate"];
    		}
    	} else {
    		$art["maj"] = "";
    		$art["publi"] = $tmp["artDate"];
    	}
    	?>';
    }


	/**
	* Méthode qui affiche la date de publication originale selon le format que l'on désire
	* 
	* @param string format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_month, #num_year(4), #num_year(2))
	* @return string la date de publication formatée
	* @author Cyril MAGUIRE
	*/		
	public function artPubliDate($format) {
		$plxMotor = plxMotor::getInstance();
		if($format == NULL && $plxMotor->plxRecord_arts->f("publi") != $plxMotor->plxRecord_arts->f("maj")) {
			$format = "Publié le #day #num_day #month #num_year(4)";
		}
		if ($plxMotor->plxRecord_arts->f("publi") != NULL && $plxMotor->plxRecord_arts->f("publi") != $plxMotor->plxRecord_arts->f("maj"))
			echo plxDate::formatDate($plxMotor->plxRecord_arts->f("publi"),$format);
    }

	/**
	* Méthode qui affiche la date de mise à jour selon le format que l'on désire
	* 
	* @param string format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_month, #num_year(4), #num_year(2))
	* @return string la date de mise à jour formatée
	* @author Cyril MAGUIRE
	*/		
	public function artDateMaj($format) {
		$plxMotor = plxMotor::getInstance();
		if($format == NULL && $plxMotor->plxRecord_arts->f("publi") != $plxMotor->plxRecord_arts->f("maj")) {
			$format = "Mise à jour le #day #num_day #month #num_year(4)";
		}
		if ($plxMotor->plxRecord_arts->f("maj") != NULL && $plxMotor->plxRecord_arts->f("publi") != $plxMotor->plxRecord_arts->f("maj"))
			echo plxDate::formatDate($plxMotor->plxRecord_arts->f("maj"),$format);
    }

    /**
    * Méthode qui indique si l'on est dans un flux rss
    * 
    * @return null
    * @author Cyril MAGUIRE
    */		
    public function plxFeedDemarrageBegin() {
    	$this->feed = true;
    }

    /**
    * Méthode qui boucle sur les articles mis à jour
    * 
    * @param integer Nombre d'articles mis à jour à afficher
    * @return array Tableau des articles mis à jour
    * @author Cyril MAGUIRE
    */		
    public function loopOnMaj($nbArticles) {
    	if ($nbArticles == NULL) {
    		$nbArticles = 5;
    	}
    	$plxMotor = plxMotor::getInstance();
    	$Maj = array();
    	$i = 0;
    	foreach ($plxMotor->activeArts as $article => $ok) {
    		if (isset($this->majList[$article])) {
    			$art = $plxMotor->parseArticle(PLX_ROOT.$plxMotor->aConf['racine_articles'].$plxMotor->plxGlob_arts->aFiles[$article]);
    			$art['url_rewrite'] = substr($art['template'],0,-4).intval($art['numero']).'/'.plxUtils::title2url($art['title']);
    			$Maj[$art['maj']] = $art;
    			$i++;
    		}
    	}
    	krsort($Maj);
    	return array_slice($Maj,0,$nbArticles);
    }
}
?>