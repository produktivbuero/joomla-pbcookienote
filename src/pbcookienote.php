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

    if ($this->app->getName() != 'site' || isset($_COOKIE['pbCookieNote'])) {
        return true;
    }

    // load language from the backend
    $lang = JFactory::getLanguage();
    $lang->load('plg_'.$this->_type.'_'.$this->_name, JPATH_ADMINISTRATOR);

    // plugin parameters
    $itemid = $this->params->get('itemid');
    $link = $this->params->get('link', '1');
    $position = $this->params->get('position', 'bottom');

    $href = $link ? JRoute::_("index.php?Itemid={$itemid}") : '';
    
    // view
    $path = JPluginHelper::getLayoutPath($this->_type, $this->_name);
    ob_start();
    include $path;
    $insert = ob_get_clean();

    $buffer = $this->app->getBody();
    if ( $position == 'top' ) {
      $buffer = preg_replace('/<body([^>]*)>/i', '<body$1>'.$insert, $buffer);
    }
    else {
      $buffer = str_ireplace('</body>', $insert.'</body>', $buffer);
    }

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

    if ($this->app->getName() != 'site' || isset($_COOKIE['pbCookieNote'])) {
        return true;
    }

    $doc = JFactory::getDocument();
    
    // plugin parameters
    $position = $this->params->get('position', 'bottom');
    $backgroundcolor = $this->params->get('backgroundcolor');
    $buttoncolor = $this->params->get('buttoncolor');
    $textcolor = $this->params->get('textcolor');

    // basic styles
    $style = '
        #pb-cookies { position: fixed; z-index: 9999; color: '.$textcolor.'; background: '.$backgroundcolor.'; }
        #pb-cookies a { color: '.$textcolor.'; text-decoration: underline; }
        #pb-cookies .pb-text { padding: 1rem; flex: 1; }
        #pb-cookies .pb-dismiss { padding: 1rem; }
        #pb-cookies .pb-dismiss button { color: '.$textcolor.'; padding: 0.5rem 1rem; background: '.$buttoncolor.'; border: 0; border-radius: 3px; }
      ';

    // add styles depending on position
    switch ($position) {
      case 'top':
        $style .= '
            #pb-cookies { left: 0; right: 0; top: 0; display: flex; align-items: center; }
          ';
        break;
      
      case 'left':
        $style .= '
            #pb-cookies { left: 0; bottom: 0; max-width: 25rem; }
            #pb-cookies .pb-dismiss { padding-top: 0; }
            #pb-cookies .pb-dismiss button { width: 100%; }
          ';
        break;

      case 'right':
        $style .= '
            #pb-cookies { right: 0; bottom: 0; max-width: 25rem; }
            #pb-cookies .pb-dismiss { padding-top: 0; }
            #pb-cookies .pb-dismiss button { width: 100%; }
          ';
        break;
      
      case 'bottom':
      default:
        $style .= '
          #pb-cookies { left: 0; right: 0; bottom: 0; display: flex; align-items: center; }
          ';
        break;
    }
    $doc->addStyleDeclaration( $style );

    // basic scripts
    $script = '
        window.addEventListener("load", function () {
          document.getElementById("pb-button").onclick = function () {
            buttonClick();
          }
        });

        function buttonClick () {
            var d = new Date();
            d.setTime(d.getTime() + (365*24*60*60*1000)); /* 365 days */
            var expires = "expires="+ d.toUTCString();
            document.cookie = "pbCookieNote=true;" + expires + ";path=/";

            if (window.jQuery) { 
              jQuery("#pb-cookies").fadeOut("slow");
            }
            else {
              document.getElementById("pb-cookies").style.display = "none";
            }
        }
      ';
    
    // add scripts depending on position
    if ( $position == 'top' || $position == 'bottom' ) { 
      $script .= '
          window.addEventListener("load", function () {
            if (window.jQuery) { 
              var height = jQuery("#pb-cookies").outerHeight();
              jQuery("body").css("padding-'.$position.'", height);
            }
            else {
              var height = document.getElementById("pb-cookies").offsetHeight;
              document.body.style.padding'.ucfirst($position).' = height + "px";
            }

            document.getElementById("pb-button").onclick = function () {
              buttonClick();
              
              if (window.jQuery) {
                jQuery("body").css("padding-'.$position.'", "");
              }
              else {
                document.body.style.removeProperty("padding-'.$position.'");
              }
            }
          });
        ';
    }
    $doc->addScriptDeclaration( $script );
  }
}
