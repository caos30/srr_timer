
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns='http://www.w3.org/1999/xhtml'>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?= $config['page_title'] ?></TITLE>
        <link rel="stylesheet" type="text/css" href="imagenes/estilos.css">
        <script src="jquery-1.10.2.min.js"></script>
    </head>
    <body>
        <div id="header_wrapper">
            <div style='float:right;right:0px;'>
                <ul style='margin:0px;'>
                    <li><a href='http://imasdeweb.indefero.net/p/class-SRR-database-sim/doc' target='_blank'>Official documentation</a></li>
                    <li><a href='http://imasdeweb.com/opensource/php_SRR_database_sim/demo/' target='_blank'>Last version & demo</a></li>
                    <li><a href='index.php?pag=logout'>Logout</a></li>
                </ul>
            </div>
            <div style='margin:0px; width:300px;white-space:nowrap;'>
                <h1><?= $config['page_title'] ?> <?= $db->version ?></h1>
                <form id="form_change_db" action="index.php">
                    <?php $path = str_replace('../','',$config['db_path']) ?>
                    <span>Database: &nbsp; 
                    <select class="a_c" name="db_filename" onchange="$('#bt_change_db').fadeIn()">
                        <?php $db_files = c_db_available_list() ?>
                        <?php foreach ($db_files as $dbf){ ?>
                        <option value="<?= htmlspecialchars($dbf[0]) ?>" <?= ($dbf[0]==$config['db_filename'])?"selected='selected'":"" ?>>
                                <?= $path.$dbf[0].' ('.c_bytes_format($dbf[1]).')' ?>
                        </option>
                        <?php }?>
                    </select>
                    <a id="bt_change_db" href="#" onclick="$('#form_change_db').submit();" style="display:none;"> &larr; click for load it</a>
                </form>

            </div>
            <div style='float:none;clear:both;'></div>
        </div>
        <div style='color:#c40000'><?= isset($error_msg) ? $error_msg : '' ?><br /></div>
        <?= $body ?>
        <?= c_test() ?>
    </body>
</html>