<?php
/**
 * DokuWiki Plugin bubble (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Robert McLeod <hamstar@telescum.co.nz>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'action.php';

class action_plugin_bubble extends DokuWiki_Action_Plugin {

    public function register(Doku_Event_Handler &$controller) {

       $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
   
    }

    public function handle_action_act_preprocess(Doku_Event &$event, $param) {

    	$this->debug_msg();

    	switch ( $event->data ) {
    		case "edit":
    			$this->_bubblify_page();
    			break;
    		case "denied":
    		case "show":
    			$this->_check_if_page_in_bubble();
    			break;
    		default:
    			break;
    	}
    }

    private function debug_msg() {
		
		global $INFO;
		$user = $INFO['userinfo'];
		$login = $INFO['client'];
		msg("referer:{$_SERVER['HTTP_REFERER']}")
		msg("login:$login");
		msg("action:{$event->data}");
		msg("grps:".implode(",",$user['grps']));
		msg("exists:".( $INFO['exists'] ? "yes": "no"));
		msg("ns:".$INFO['namespace']);
    }
    private function _bubblify_page() {

    	global $INFO;
    	$user = $INFO['userinfo'];
    	$login = $INFO['client'];

    	// Don't bubblify admins
    	if ( array_search($user['grps'], "admin") !== FALSE )
    		return;

    	// Don't bubblify existing pages
    	if ( $INFO['exists'] === TRUE )
    		return;

    	// The user bubbled themselves
    	if ( $INFO['namespace'] == $login )
    		return;

    	// Set the page name
		$ID = "$login:$ID";
		$INFO['id'] = $ID;

		msg("This page has been saved in your namespace");
    }

    private function _check_if_page_in_bubble() {

    	global $INFO;
		$user = $INFO['userinfo'];
		$login = $INFO['client'];

		// Already in the bubble
		if ( $INFO['namespace'] == $login )
			return;

		// User has not this page
		if ( file_exists(DOKU_DATA."pages/$login/$ID") === FALSE )
			return;

		msg("You also have a page called $ID in your namespace: <a href='/?id=$login:$ID&do=show'>$login:$ID</a>");
    }

}




// vim:ts=4:sw=4:et:
