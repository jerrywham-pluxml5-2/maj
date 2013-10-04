<?php 
/**
 * Plugin Maj
 *
 * @package	PLX
 * @version	1.5
 * @date	07/08/2012
 * @author	Cyril MAGUIRE
 **/
if(!defined('PLX_ROOT')) exit; 


# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
	$plxPlugin->setParam('automaj', $_POST['automaj'], 'cdata');
	$plxPlugin->setParam('replace', $_POST['replace'], 'cdata');
	$plxPlugin->saveParams();
	header('Location: parametres_plugin.php?p=maj');
	exit;
}
$automaj =  $plxPlugin->getParam('automaj')=='' ? '' : $plxPlugin->getParam('automaj');
$replace =  $plxPlugin->getParam('replace')=='' ? '' : $plxPlugin->getParam('replace');
?>

<h2><?php echo $plxPlugin->getInfo('title') ?></h2>

<form action="parametres_plugin.php?p=maj" method="post">
	<fieldset>
		<p class="field"><label for="automaj"><?php echo $plxPlugin->getLang('L_CONFIG_AUTOMAJ') ?>&nbsp;:</label>
			<input type="checkbox" name="automaj" id="automaj" <?php echo ($automaj == 'on') ? ' checked="checked"' : '';?> />
		</p>
		<p class="field"><label for="replace"><?php echo $plxPlugin->getLang('L_CONFIG_MAJ_REPLACE_DATE') ?>&nbsp;:</label>
			<input type="checkbox" name="replace" id="replace" <?php echo ($replace == 'on') ? ' checked="checked"' : '';?> />
		</p>
		<p>
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>
