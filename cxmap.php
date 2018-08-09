<?php
/*
Plugin Name: cxmap
Description:
Version: 1.0.0
Author: CXMap
Author URI: http://cxmap.io
*/

/*  Copyright 2015  Joomline  (email: sale@joomline.ru)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define( 'CXMAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CXMAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once (CXMAP_PLUGIN_DIR . 'includes/cxmap.class.php');

add_action( 'init', array( 'cxmap', 'init' ) );
load_plugin_textdomain( 'cxmap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );