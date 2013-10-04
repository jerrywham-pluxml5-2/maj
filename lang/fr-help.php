<?php 
/**
 * Plugin Maj
 *
 * @package	PLX
 * @version	1.5
 * @date	07/08/2012
 * @author	Cyril MAGUIRE
 **/
if(!defined('PLX_ROOT')) exit; ?>
<style type="text/css">
pre {
	font-size:14px;
	border: 1px solid #999;
	padding: 20px;
	white-space: pre-wrap;
	word-wrap: break-word;
	background-color: #FFF;
}
.red {color:red;}
</style>

<h2>Aide</h2>
<p>Fichier d&#039;aide du plugin Maj (Mise à jour)</p>

<p>&nbsp;</p>
<h2>Installation</h2>
<p>Pensez &agrave; activer le plugin.<br/>
Dans la page de configuration, il est possible de modifier l'emplacement du fichier qui enregistre les mises à jour. Par défaut, lors de l'activiation du plugin, c'est le fichier 0000.maj.xml situé dans le dossier data/articles/ qui est utilisé.</p>
<h2>Utilisation</h2>
<p>La date de mise à jour peut être automatiquement enregistrée ou enregistrée manuellement, en fonction de la configuration choisie.</p>
<p>Le plugin dispose de deux modes de fonctionnement : 1) soit la date de publication n'est pas modifiée par la date de mise à jour, 2) soit elle l'est.</p>
<p>Le choix se fait dans la partie configuration du plugin en cochant ou décochant la case dédiée.</p>
<h3>La date de publication est modifiée par la date de mise à jour</h3>
<p>Cette modification a lieu dans la partie publique du site. Ainsi, les anciens articles mis à jour peuvent revenir sur le devant de la scène. La date de parution est alors la date de mise à jour.</p>
<p>La date originelle de publication n'est pas pour autant perdue : on peut l'afficher par l'intermédiaire du hook suivant :</p>
<pre>&lt;?php eval($plxShow->callHook('artPubliDate', 'Initialement publié le #num_day #month #num_year(4)')); ?&gt;</pre>
<p>Le paramètre format est optionnel. Les tags à utiliser sont les mêmes que ceux de la fonction native de pluxml "$plxShow->artDate".</p>
<p>Dans la partie administration, rien n'est modifié. Le tableau récapitulatif des articles est toujours le même.</p>
<p>Dans la page d'édition des articles, un champ a été ajouté dans le bas de la sidebar, pour enregistrer la date de mise à jour. Cela se fait automatiquement à chaque édition.</p>
<p style="text-align:center;padding:10px;"><img src="<?php echo PLX_PLUGINS;?>maj/img/sidebar-maj.png" alt="sidebar"/></p>
<h3>La date de publication n'est pas modifiée par la date de mise à jour</h3>
<p>Le fonctionnement de pluxml n'est pas modifié.</p>
<p>On peut récupérer la date de mise à jour par le hook suivant :</p>
<pre>&lt;?php eval($plxShow->callHook('artDateMaj', 'Mise à jour le #num_day #month #num_year(4)')); ?&gt;</pre>
<p>Le paramètre format est optionnel. Les tags à utiliser sont les mêmes que ceux de la fonction native de pluxml "$plxShow->artDate".</p>
<p>Ces deux hooks fonctionnent quel que soit le mode choisi pour le plugin.</p>
<h3>Boucler sur les articles mis à jour</h3>
<p>Afin de n'afficher que les articles mis à jour, on peut utiliser le hook suivant :</p>
<pre>&lt;?php $plxShow->callHook('loopOnMaj', 3); ?&gt;</pre>
<p>Le paramètre correspond au nombre maximal d'articles à afficher. Il est optionnel. Par défaut il vaut 5.</p>
<p>Il est à noter que les articles mis à jour seront afficher deux fois si la boucle standard de pluxml est utilisée également.</p>
<p>La fonction retourne le tableau suivant dont les résultats pourront être mis en forme facilement :</p>
<pre> 
Array
(
    [0] => Array
        (
            [title] => 
            [allow_com] => 
            [template] => 
            [chapo] => 
            [content] => 
            [tags] => 
            [meta_description] => 
            [meta_keywords] => 
            [title_htmltag] => 
            [filename] => 
            [numero] => 
            [author] => 
            [categorie] => 
            [url] => 
            [date] => 
            [nb_com] => 
            [format] => 
            [password] => 
            [publi] => 
            [maj] => 
        )
    (...)
)
</pre>
<p>L'index le plus petit correspond à l'article le plus récent.</p>
<p>Pour appeler, par exemple le titre, il suffit de faire :</p>
<pre>&lt;?php 
	$artMaj = $plxShow->callHook('loopOnMaj', 3);
	foreach($artMaj as $art) {
		echo $art['title'].'&lt;br/&gt;';
	}
?&gt;</pre>
<p>Il vaut mieux stocker le résultat du hook dans une variable afin d'éviter à chaque tour de boucle de parcourir à nouveau le fichier de mise à jour.</p>