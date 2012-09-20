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
		msg("referer:{$_SERVER['HTTP_REFERER']}");
		msg("login:$login");
		msg("action:{$event->data}");
		msg("grps:".implode(",",$user['grps']));
		msg("exists:".( $INFO['exists'] ? "yes": "no"));
		msg("ns:".$INFO['namespace']);
    }

    /**
     * Detects if a user is creating a new page, and if so redirects
     * them so they are editing the page in their namespace
     *
     * i.e. user edits ?id=my_page and is redirected to ?id=username:my_page
     */
    private function _bubblify_page() {

		global $INFO;
		$ID = $INFO['id'];
		$user = $INFO['userinfo'];
		$login = $INFO['client'];

		// Don't bubblify admins
		if ( $this->_user_is_admin($user) )
    		return;

    	// Don't bubblify existing pages
    	if ( $INFO['exists'] === TRUE )
    		return;

    	// The user bubbled themselves
    	if ( $INFO['namespace'] == $login )
    		return;

    	// Send them to their own namespace
		send_redirect(DOKU_URL."doku.php?id=$login:$ID&do=edit");
    }

    /**
     * Checks if the current page is in the users page and does a few thing:
     * 
     * if the user is already in their namespace - does nothing
     * if the page doesn't exist - redirect to the same page in their namespace
     * if the user doesn't have this page in their namespace - do nothing
     * if the user has this page in their namespace - show message telling them that
     */
    private function _check_if_page_in_bubble() {

    	global $INFO;
    	$ID = $INFO['id'];
		$user = $INFO['userinfo'];
		$login = $INFO['client'];

		// Don't bubblify admins
    	if ( $this->_user_is_admin($user) )
    		return;

		// Already in the bubble
		if ( $INFO['namespace'] == $login )
			return;

		// Page does not exist, redirect to namespace page
		if ( $INFO['exists'] === FALSE )
			send_redirect(DOKU_URL."doku.php?id=$login:$ID");

		// User has not this page, so let them view it in peace
		if ( file_exists(DOKU_INC."data/pages/$login/$ID") === FALSE )
			return;

		// show the message
		msg("You also have a page called $ID in your namespace: <a href='".DOKU_URL."doku.php?id=$login:$ID&do=show'>$login:$ID</a>");
    }

    private function _user_is_admin( $user ) {

    	return is_int( array_search( "admin", $user['grps'] ) );
    }

}




// vim:ts=4:sw=4:et:
