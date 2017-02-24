<?php
/*
Plugin Name: Block VCS Updates
Description: Prevents updating plugins and themes that are VCS checkouts (GIT, SVN, Mercurial, Bazaar)
Version: 1.0
Author: Aaron Edwards
Author URI: https://uglyrobot.com/
Network: true
 */

/*
Copyright 2017 Aaron Edwards

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class Block_VCS_Updates {

	private $vcs_dirs = array();

	public function __construct() {
		$this->vcs_dirs = array( '.svn', '.git', '.hg', '.bzr' );

		add_action( 'site_transient_update_plugins', array( $this, 'plugins' ), 201 );
		add_action( 'site_transient_update_themes', array( $this, 'themes' ), 201 );
	}

	public function plugins( $value ) {
		if ( isset(  $value->response ) && is_array( $value->response ) && count( $value->response ) ) {
			foreach ( $value->response as $filename => $plugin ) {
				$plugin_dir = wp_normalize_path( WP_PLUGIN_DIR );

				$git_path = trailingslashit( $plugin_dir ) . plugin_dir_path( $filename );
				foreach ( $this->vcs_dirs as $dir ) {
					if ( @is_dir( $git_path . $dir ) ) {
						$value->response[ $filename ]->package = '';
					}
				}
			}
		}

		return $value;
	}

	public function themes( $value ) {
		if ( isset(  $value->response ) && is_array( $value->response ) && count( $value->response ) ) {
			foreach ( $value->response as $slug => $theme ) {
				$git_path = trailingslashit( trailingslashit( get_theme_root( $slug ) ) . $slug );
				foreach ( $this->vcs_dirs as $dir ) {
					if ( @is_dir( $git_path . $dir ) ) {
						$value->response[ $slug ]['package'] = '';
					}
				}
			}
		}

		return $value;
	}
}

$_GLOBALS['Block_VCS_Updates'] = new Block_VCS_Updates();