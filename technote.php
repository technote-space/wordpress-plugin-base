<?php
/*  Copyright 2018 technote-space (email : technote.space@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}

define( 'TECHNOTE_PLUGIN', 'technote' );

define( 'TECHNOTE_BOOTSTRAP', __FILE__ );

define( 'TECHNOTE_VERSION', '0.0.0.0.0' );

if ( ! defined( 'DS' ) ) {
	define( 'DS', DIRECTORY_SEPARATOR );
}

require_once dirname( __FILE__ ) . DS . 'classes' . DS . 'technote.php';
