<?php
/**
 * @package    PB Cookie Note
 *
 * @author     Sebastian Brümmer <sebastian@produktivbuero.de>
 * @copyright  Copyright (C) 2018 *produktivbüro . All rights reserved
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$lang = JFactory::getLanguage();

?>

<div id="pb-cookies">
  <div class="pb-text">
    <?php echo JText::_('PLG_SYSTEM_PBCOOKIENOTE_MESSAGE'); ?>
    <?php if ( $href) : ?><a href="<?php echo $href; ?>"><?php echo JText::_('PLG_SYSTEM_PBCOOKIENOTE_LINK'); ?></a><?php endif; ?>
  </div>
  <div class="pb-dismiss">
    <button id="pb-button"><?php echo JText::_('PLG_SYSTEM_PBCOOKIENOTE_BUTTON'); ?></button>
  </div>
</div>
