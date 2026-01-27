<?php
echo "<h1>PHP Configuration Check</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Loaded Configuration File: " . php_ini_loaded_file() . "</p>";
echo "<p>GD Extension Loaded: " . (extension_loaded('gd') ? '<strong style="color:green">YES</strong>' : '<strong style="color:red">NO</strong>') . "</p>";

if (function_exists('gd_info')) {
    echo "<pre>";
    print_r(gd_info());
    echo "</pre>";
} else {
    echo "<p>gd_info() function does not exist.</p>";
}
