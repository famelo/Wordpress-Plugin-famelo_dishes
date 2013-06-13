<?php
/*
Plugin Name: Automatic Domain Change
Description: Automatically changes the domain of a WordPress blog
Author: Marc Neuhaus <mneuhaus@famelo.com>
Version: 0.0.1
*/

// --

/**
 * Automatic Domain Changer class
 *
 * @author	Tommy Lacroix <tlacroix@nuagelab.com>
 */
class FameloDomainChange {
	public function __construct() {
		if (!defined('WP_ADMIN')) {
			// Avoid side-effects by only executing in the backend
			return;
		}

		if (get_option('famelo_wordpress_uri') === FALSE) {
			add_option('famelo_wordpress_uri', $this->getWordpressUri());
		}
		if (get_option('famelo_wordpress_path') === FALSE) {
			add_option('famelo_wordpress_path', $this->getWordpressPath());
		}

		if ($this->hasDomainChanged() && $this->hasPathChanged()) {
			$oldDomain = $this->parseDomain(get_option('famelo_wordpress_uri'));
			$newDomain = $this->parseDomain($this->getWordpressUri());
			$this->updateDomainReferences($oldDomain, $newDomain);
			update_option('famelo_wordpress_path', $this->getWordpressPath());
			update_option('famelo_wordpress_uri', $this->getWordpressUri());
			echo '
			<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
			<style>
			.wrapper {
				width: 800px;
				margin: 100px auto 0;
				overflow: hidden;
				background: #f7f7f7;
				border: 1px solid #dfdfdf;
				padding: 30px;
			}
			table {
				font-size: 12px;
				background: white;
			}
			</style>
			<div class="wrapper">
				<h3>The Domain has been automatically changed!</h3>
				<div class="alert alert-success">
  					A Domain + Path change was detected:
				</div>
				<table class="table table-condensed table-bordered">
				<tr>
					<th></th><th>Old</th><th>New</th>
				</tr>
				<tr>
					<th>Domain</th>
					<td>' . $oldDomain . '</td>
					<td>' . $newDomain . '</td>
				</tr>
				<tr>
					<th>Path</th>
					<td>' . get_option('famelo_wordpress_path') . '</td>
					<td>' . $this->getWordpressPath() . '</td>
				</tr>
				</table>
			<strong>You can now reload to continue :)</strong>
			</div>';
			exit();
		}
	}

	public function hasDomainChanged() {
		return get_option('famelo_wordpress_uri') != $this->getWordpressUri();
	}

	public function hasPathChanged() {
		return get_option('famelo_wordpress_path') != $this->getWordpressPath();
	}

	public function getWordpressUri() {
		$env = $GLOBALS['HTTP_SERVER_VARS'];
		return 'http' . ($env['SSL_SESSION_ID'] ? 's' : '') . '://' . $env['HTTP_HOST'] . ( php_sapi_name() == 'cgi' ? $env['PATH_INFO'] : $env['SCRIPT_NAME'] );
	}

	public function getWordpressPath() {
		$env = $GLOBALS['HTTP_SERVER_VARS'];
		$path = str_replace('//', '/', str_replace('\\', '/', php_sapi_name() == 'cgi' || php_sapi_name() == 'isapi' ? $env['PATH_TRANSLATED'] : $env['SCRIPT_FILENAME']));
		preg_match('/(.*?)(wp-.+|index.php)/', $path, $match);
		return $match[1];
	}

	public function parseDomain($uri) {
		preg_match('/(.+)\/.*\.php$/', $uri, $match);
		return str_replace('/wp-admin', '', $match[1]);
	}

	public function updateDomainReferences($old, $new) {
		mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		mysql_select_db(DB_NAME);
		mysql_query('SET NAMES ' . DB_CHARSET);
		if (function_exists('mysql_set_charset')) {
			mysql_set_charset(DB_CHARSET);
		}

		$tables = mysql_query('SHOW TABLES;');
		while ($row = mysql_fetch_assoc($tables)) {
			$table = current($row);

			// Skip if the table name doesn't match the wordpress prefix
			if (substr($t, 0, strlen($wpdb->prefix)) != $wpdb->prefix) {
				continue;
			}

			// Get table indices
			$id = NULL;
			$indexes = mysql_query('SHOW INDEX FROM ' . $table);
			while ($row = mysql_fetch_assoc($indexes)) {
				if ($row['Key_name'] == 'PRIMARY') {
					$id = $row['Column_name'];
					break;
				} elseif ($row['Non_unique'] == 0) {
					$id = $row['Column_name'];
				}
			}
			if ($id === NULL) {
				// No unique index found, skip table.
				continue;
			}
			$rows = mysql_query('SELECT * FROM ' . $table);
			while ($row = mysql_fetch_assoc($rows)) {
				$fields = array();
				$sets = array();
				// Process all columns
				foreach ($row as $k => $v) {
					$ov = $v;
					$sv = unserialize($v);
					if ($sv) {
						// Column value was serialized
						$v = $sv;
						$serialized = TRUE;
					} else {
						// Column value was not serialized
						$serialized = FALSE;
					}

					// Replace
					$v = $this->replace($old, $new, $v);

					// Reserialize if needed
					if ($serialized === TRUE) {
						$v = serialize($v);
					}

					// If value changed, replace it
					if ($ov != $v) {
						$sets[] = '`' . $k . '`="' . mysql_real_escape_string($v) . '"';
					}
				}

				// Update table if we have something to set
				if (count($sets) > 0) {
					$sql = 'UPDATE ' . $table . ' SET ' . implode(',', $sets) . ' WHERE `' . $id . '`=' . $row[$id] . ' LIMIT 1;';
					mysql_query($sql);
				}
			}

		}
	}

	public function replace($search, $replace, $subject) {
		if (is_array($subject)) {
			foreach ($subject as $key => $value) {
				$subject[$key] = $this->replace($search, $replace, $value);
			}
		} elseif (is_object($subject)) {
			foreach ($subject as $key => $value) {
				$subject->$key = $this->replace($search, $replace, $value);
			}
		} elseif (is_string($subject)) {
			$searchWithWWW = str_replace('://', '://www.', $search);
			$subject = str_replace($oldWithWWW, $replace, $subject);
			$subject = str_replace($search, $replace, $subject);
		}
		return $subject;
	}
}


$changer = new FameloDomainChange();

?>