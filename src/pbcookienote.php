<?php
/**
 * @package    PB Cookie Note
 *
 * @author     Sebastian Brümmer <sebastian@produktivbuero.de>
 * @copyright  Copyright (C) 2018 *produktivbüro . All rights reserved
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * PbCookieNote plugin.
 *
 * @package  PB Cookie Note
 * @since    1.0
 */
class plgSystemPbCookieNote extends CMSPlugin
{
  
  /**
   * Application object
   *
   * @var    CMSApplication
   * @since  1.0
   */
  protected $app;

  /**
   * Load the language file on instantiation
   *
   * @var    boolean
   * @since  3.1
   */
  protected $autoloadLanguage = true;

	/**
	 * onAfterRender.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{

    if ($this->app->getName() != 'site' || isset($_COOKIE['pb-cookie-note'])) {
        return true;
    }

    // load language from the backend
    $lang = JFactory::getLanguage();
		$lang->load('plg_'.$this->_type.'_'.$this->_name, JPATH_ADMINISTRATOR);

		// plugin parameters
		$itemid = $this->params->get('itemid');
		$link = $this->params->get('link', '1');

    $href = $link ? JRoute::_("index.php?Itemid={$itemid}") : '';
    
    // view
    $path = JPluginHelper::getLayoutPath($this->_type, $this->_name);
		ob_start();
		include $path;
		$insert = ob_get_clean();

    $buffer = $this->app->getBody();
    $buffer = str_ireplace('</body>', $insert.'</body>', $buffer);

    $this->app->setBody($buffer);

    return true;
	}

	/**
	 * onBeforeCompileHead.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onBeforeCompileHead()
	{

		if ($this->app->getName() != 'site' || isset($_COOKIE['pb-cookie-note'])) {
        return true;
    }

    $doc = JFactory::getDocument();
		
		// plugin parameters
		$backgroundcolor = $this->params->get('backgroundcolor', '');
		$buttoncolor = $this->params->get('buttoncolor', '');
		$textcolor = $this->params->get('textcolor', '');

		// styles
    $style = '
	    	#pb-cookies { color: '.$textcolor.'; position: fixed; z-index: 9999; left: 0; right: 0; bottom: 0; display: flex; align-items: center; background: '.$backgroundcolor.'; }
	      #pb-cookies a { color: '.$textcolor.'; text-decoration: underline; }
	      #pb-cookies .pb-text { padding: 1rem; flex: 1; }
	      #pb-cookies .pb-dismiss { padding: 1rem; }
	      #pb-cookies .pb-dismiss button { color: '.$textcolor.'; padding: 0.5rem 1rem; background: '.$buttoncolor.'; border: 0; border-radius: 3px; }
	    ';
    $doc->addStyleDeclaration( $style );

		// scripts
    $script = '
				window.addEventListener("load", function () {
					if (window.jQuery) { 
				  	var height = jQuery("#pb-cookies").outerHeight();
				  	jQuery("body").css("padding-bottom", height);
				  }
				  else {
			      var height = document.getElementById("pb-cookies").offsetHeight;
			      document.body.style.paddingBottom = height + "px";
				  }

				  document.getElementById("pb-button").onclick = function () {
				  	var d = new Date();
				    d.setTime(d.getTime() + (365*24*60*60*1000)); /* 365 days */
				    var expires = "expires="+ d.toUTCString();
				    document.cookie = "pb-cookie-note=true;" + expires + ";path=/";

				    if (window.jQuery) { 
				    	jQuery("#pb-cookies").fadeOut("slow");
				    	jQuery("body").css("padding-bottom", "");
				    }
				    else {
				      document.getElementById("pb-cookies").style.display = "none";
				      document.body.style.removeProperty("padding-bottom");
				    }
				  }
				});
    	';
    	$doc->addScriptDeclaration( $script );
	}
}
