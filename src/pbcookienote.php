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
   * This function is called on initialization.
   *
   * @return  void.
   *
   * @since   1.0
   */

  public function __construct(&$subject, $config = array())
  {

    parent::__construct($subject, $config);

    $this->cookienote = array();
    $this->cookienote['cookie'] = 'pb-cookie-note';

    // plugin parameters
    $params = new JRegistry($config['params']);
    
    $this->cookienote['itemid'] = $params->get('itemid');
    $this->cookienote['position'] = $this->params->get('position', 'bottom');
    $this->cookienote['layout'] = $this->params->get('layout', 'default');
    $this->cookienote['backgroundcolor'] = $params->get('backgroundcolor', '');
    $this->cookienote['buttoncolor'] = $params->get('buttoncolor', '');
    $this->cookienote['textcolor'] = $params->get('textcolor', '');

  }

  /**
   * onAfterRender.
   *
   * @return  void.
   *
   * @since   1.0
   */
  public function onAfterRender()
  {

    $cookie = $this->cookienote['cookie'];
    
    if ($this->app->getName() != 'site' || isset($_COOKIE[$cookie])) {
        return true;
    }

    // load language from the backend
    $lang = JFactory::getLanguage();
    $lang->load('plg_'.$this->_type.'_'.$this->_name, JPATH_ADMINISTRATOR);

    $href = $this->cookienote['itemid'] ? JRoute::_("index.php?Itemid={$this->cookienote['itemid']}") : '';
    
    // view
    $path = JPluginHelper::getLayoutPath($this->_type, $this->_name);
    ob_start();
    include $path;
    $insert = ob_get_clean();

    $buffer = $this->app->getBody();
    if ( $this->cookienote['position'] == 'top' ) {
      $buffer = preg_replace('/<body([^>]*)>/i', '<body$1>'.$insert, $buffer);
    }
    else {
      $buffer = str_ireplace('</body>', $insert.'</body>', $buffer);
    }

    $this->app->setBody($buffer);
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

    $cookie = $this->cookienote['cookie'];
    
    if ($this->app->getName() != 'site' || isset($_COOKIE[$cookie])) {
        return true;
    }

    $doc = JFactory::getDocument();

    // basic styles
    $style = '
        #pb-cookies { position: fixed; z-index: 9999; color: '.$this->cookienote['textcolor'].'; background: '.$this->cookienote['backgroundcolor'].'; }
        #pb-cookies a { color: '.$this->cookienote['textcolor'].'; text-decoration: underline; }
        #pb-cookies .pb-text { padding: 1rem; flex: 1; }
        #pb-cookies .pb-dismiss { padding: 1rem; }
        #pb-cookies .pb-dismiss button { color: '.$this->cookienote['textcolor'].'; padding: 0.5rem 1rem; background: '.$this->cookienote['buttoncolor'].'; border: 0; border-radius: 3px; cursor: pointer; }
      ';

    // add styles depending on position
    switch ( $this->cookienote['position'] ) {
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

    switch ( $this->cookienote['layout'] ) {
      case 'condensed':
        if ( $this->cookienote['position'] == 'top' || $this->cookienote['position'] == 'bottom' ) {
          $style .= '
              #pb-cookies .pb-text { padding-top: 0; padding-bottom: 0; }
              #pb-cookies .pb-dismiss { padding-top: 0; padding-bottom: 0; padding-right: 0; }
              #pb-cookies .pb-dismiss button { border-radius: 0; }
            ';
         }
        elseif ( $this->cookienote['position'] == 'left' || $this->cookienote['position'] == 'right' ) {
          $style .= '
              #pb-cookies .pb-dismiss { padding-left: 0; padding-right: 0; padding-bottom: 0; }
              #pb-cookies .pb-dismiss button { border-radius: 0; }
            ';
         }
        break;
      
      case 'simple':
        $style .= '
            #pb-cookies .pb-dismiss button { color: '.$this->cookienote['buttoncolor'].'; background: transparent; border: 2px solid '.$this->cookienote['buttoncolor'].'; }
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
            document.cookie = "'.$cookie.'=true;" + expires + ";path=/";

            if (window.jQuery) { 
              jQuery("#pb-cookies").fadeOut("slow");
            }
            else {
              document.getElementById("pb-cookies").style.display = "none";
            }
        }
      ';
    
    // add scripts depending on position
    if ( $this->cookienote['position'] == 'top' || $this->cookienote['position'] == 'bottom' ) { 
      $script .= '
          window.addEventListener("load", function () {
            if (window.jQuery) { 
              var height = jQuery("#pb-cookies").outerHeight();
              jQuery("body").css("padding-'.$this->cookienote['position'].'", height);
            }
            else {
              var height = document.getElementById("pb-cookies").offsetHeight;
              document.body.style.padding'.ucfirst($this->cookienote['position']).' = height + "px";
            }

            document.getElementById("pb-button").onclick = function () {
              buttonClick();

              if (window.jQuery) {
                jQuery("body").css("padding-'.$this->cookienote['position'].'", "");
              }
              else {
                document.body.style.removeProperty("padding-'.$this->cookienote['position'].'");
              }
            }
          });
        ';
    }

    $doc->addScriptDeclaration( $script );
  }
}
