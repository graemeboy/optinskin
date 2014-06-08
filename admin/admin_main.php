<?php	
include_once 'menu/admin_menu_setup.php'; // admin menu
include_once 'addSkin/admin_add_skin.php'; // add skin page

include_once 'skinStats/admin_statistics.php'; // shows comparative statistics for skins
include_once 'exportSkin/admin_export_skin.php'; // allows skins to be exported

// Validation and License Key
// Note: removing this line will actually break the plugin.
include_once 'validation/admin_validation.php';

include_once 'admin_functions.php'; // admin functions
include_once 'customDesign/admin_custom.php'; // add and edit custom designs

?>