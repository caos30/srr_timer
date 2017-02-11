<table border='0' cellspacing='20' align='center' style='border:none!important;'>
    <tr>
        <td>
            <b>Tables' list</b>
            &nbsp; &nbsp; <b>[</b> <a href="index.php?op1=vacuum" title="This action will remove not used physical space in the database file">VACUUM database</a> <b>]</b>
            <?php if ($op1 != 'edit_table'){ ?>
            &nbsp; &nbsp; <b>[</b> <a href="#" onclick="$('#edit_table_wrapper').toggle();return false;">Add new table</a> <b>]</b>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td valign='top'>		
            <table id='content'>
                <tr>
                    <td>
                        <div id='edit_table_wrapper' style="<?= $op1 == 'edit_table' ? '':'display:none;' ?>">
                            <form id='edit_table_form' method='post' action='index.php'>
                                <input type='hidden' name='op1' value='save_table'>
                                <input type='hidden' name='table_name' value="<?= !empty($_GET['table_name']) ? $_GET['table_name'] : '' ?>">
                                <table style="width:100%;">
                                    <thead>
                                        <tr>
                                            <?php if ($op1 == 'edit_table'){ ?>
                                                <td>Edit structure table  <b><?= $_GET['table_name'] ?></td>
                                        <?php }else{ ?>
                                                <td>Add a <b>new table</b></td>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style='padding:1rem 1.5rem;'>
                                                <p style='margin:0;'>Table name:</p>
                                                <p style='margin:0.5rem;text-align:center;'><input type='text' style="width:90%;margin:5px auto;" name='new_table_name' value="<?= isset($editable_table['nombre']) ? htmlspecialchars($editable_table['nombre']) : '' ?>"></p>

                                                <p style='margin:0;'>List of fields (separated with blank space):</p>
                                                <p style='margin:0.5rem;text-align:center;'><textarea name='fields' style="width:90%;margin:5px auto;height:80px;"><?= isset($editable_table['lista_campos']) ? implode(" ", $editable_table['lista_campos']) : '' ?></textarea></p>

                                                <br />
                                                <a href="#" class='bt' onclick="$('#edit_table_form').submit();"><?= ($op1 == 'edit_table') ? 'Save changes':'Add table' ?></a> &nbsp;
                                                <a href="#" class='bt' onclick="js_import_db_click();return false;">Import SQLite db</a><br />
                                                <script>
                                                    function js_import_db_click(){
                                                        $('#form_import_table input[name=op1]').val('import_db');
                                                        $('#td_').html('Import SQLite db');
                                                        $('#table_import').show();
                                                        $('#import_file').click();
                                                    }
                                                </script>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                            <form id='form_import_table' method='post' action='index.php' enctype='multipart/form-data'>
                                <table id='table_import' style='margin-top:5px;width:100%;display:none;' cellpadding='5'>
                                    <thead>
                                        <tr>
                                            <td>
                                                <input name='op1' value='import_table' type='hidden' />
                                                <input name='table_name' id='form_import_table_table_name' value='' type='hidden' />
                                                <span id='td_'>&nbsp;</span>
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style='padding:15px; padding-top:0px;'>
                                                <br /><input type='file' id='import_file' name='import_file' style='margin-right:20px;' /><br />
                                                <br /><input type='button' value=' Send ' onclick="javascript:document.getElementById('form_import_table').submit();" />
                                                <div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                            <br />
                        </div>

                        
                        <table>
                            <thead>
                                <tr>
                                    <td>table name</td>
                                    <td style='min-width:150px;'>fields_list</td>
                                    <td>#records</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($tables)>0){ $ii=0; ?>
                                <?php foreach($tables as $table){ $ii++; ?>
                                <tr class="<?= ($ii % 2 == 0)?'tr_pair' : 'tr_odd' ?>" valign=top>
                                    <td align=right>
                                        <table border=0 width=100%>
                                            <tr>
                                                <td><a href='index.php?pag=records&table_name=<?= $table['nombre'] ?>' title='List records'><?= $table['nombre'] ?></a></td>
                                                <td width=5px><a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>" title="List records"><img src="imagenes/b_props.png" border=0></a></td>
                                                <td width=5px><a href='index.php?op1=edit_table&table_name=<?= $table['nombre'] ?>' title='edit structure of table'><img src="imagenes/b_edit.png" border=0></a></td>
                                                <td width=5px><a href='index.php?op1=empty_table&table_name=<?= $table['nombre'] ?>' onclick="if (!confirm('Please, confirm that you want to delete all the records of this table.')) {
                                                        return false;
                                                    }" title='empty table'><img src="imagenes/b_empty.png" border='0'></a></td>
                                                <td width=5px><a href='index.php?op1=duplicate_table&table_name=<?= $table['nombre'] ?>' title='duplicate table'><img src="imagenes/b_duplicate.png" border=0></a></td>
                                                <td width=5px><a href='index.php?op1=delete_table&table_name=<?= $table['nombre'] ?>' onclick="if (!confirm('Please, confirm that you want to delete this table and all its records.')) {
                                                        return false;
                                                    }" title='delete table'><img src="imagenes/b_deltbl.png" border=0></a></td>
                                                <td width=5px><a href='#table_import' onclick="javascript:document.getElementById('form_import_table_table_name').value = '<?= $table['nombre'] ?>';
                                                    $('#table_import').show();
                                                    document.getElementById('td_').innerHTML = 'Import&nbsp;<b><?= $table['nombre'] ?></b> table';
                                                    return false;" title="Import table"><img src='imagenes/b_import.png' border=0></a></td>
                                                <td width=5px><a href='index.php?op1=export_table&table_name=<?= $table['nombre'] ?>' title='Export table'><img src='imagenes/b_export.png' border=0></a></td>
                                                <td width=5px><a href='index.php?op1=export_table_csv&table_name=<?= $table['nombre'] ?>' title='Export table in a CSV file'><img src='imagenes/b_csv.png' border=0></a></td>
                                                <td width=5px><a href='index.php?pag=statistics&table_name=<?= $table['nombre'] ?>' title='Table statistics'><img src='imagenes/b_statistics.png' border=0></a></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                        <a href='#' rel='0' id='a_<?= $table['nombre'] ?>_field_list' onclick="js_toggle_field_list('<?= $table['nombre'] ?>');
                                                    return false;" style='line-height:25px;'>show fields</a> (<?= count($table['lista_campos']) ?>)
                                        <div style='display:none;' id='div_<?= $table['nombre'] ?>_field_list'>
                                            <?= implode('<br />',$table['lista_campos']) ?>
                                        </div></td>
                                    <td class="a_c"><?= number_format(intval($table['num_records']),0,'.',',') ?></td>
                                </tr>
                                <?php }} ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    
    <?php $old_db_tables = c_old_db_tables(); ?>
    <?php if (count($old_db_tables)>0) { ?>
    <tr>
        <td class='a_r'>
            <b>Old database tables</b>
            <br /><a href='index.php?op1=import_old_database'>&rarr; click here for create a clone in the current database (above)</a>
        </td>
    </tr>
    <tr>
        <td valign='top'>	
            <div class="block">
                <table border='0' cellpadding='5' align='right'>
                    <tr style="color:#ffffff;background:#000000;">
                        <td>table name</td>
                        <td style='min-width:150px;'>fields_list</td>
                        <td>#records</td>
                    </tr>
                    <?php $ii=0; foreach($old_db_tables as $table){ $ii++; ?>
                    <tr style="color:#000000; background:<?= ($ii % 2 == 0)?'#eaeaea' : '#f6f6f6' ?>" valign=top>
                        <td align=right>
                            <table border=0 width=100%>
                                <tr>
                                    <td><?= $table['nombre'] ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <a href='#' rel='0' id='a_old_db_<?= $table['nombre'] ?>_field_list' onclick="js_toggle_field_list('old_db_<?= $table['nombre'] ?>');
                                        return false;" style='line-height:25px;'>show fields</a> (<?= count($table['lista_campos']) ?>)
                            <div style='display:none;' id='div_old_db_<?= $table['nombre'] ?>_field_list'>
                                <?= implode('<br />',$table['lista_campos']) ?>
                            </div></td>
                        <td class="a_c"><?= intval($table['num_registros']) ?></td>
                    </tr>
                    <?php } ?>
                </table>
                <div class="clear"></div>
            </div>
        </td>
    </tr>
    <?php }?>
    
    <?php $JSON_db_tables = c_load_json_db_tables(); ?>
    <?php if (count($JSON_db_tables)>0) { ?>
    <tr>
        <td class='a_r'>
            <b>JSON databases (exported from PHPMyAdmin)</b>
        </td>
    </tr>
    <tr>
        <td valign='top'>		
            <?php foreach($JSON_db_tables as $db_filename => $a_db){ ?>
            <div class="block">
                <h3 class="a_r">
                    File: <u><?= $db_filename ?></u>
                    &nbsp; | &nbsp; Database name: <u><?= $a_db['db_name'] ?></u>
                </h3>
                <form id="form_import_json_database" action="index.php?op1=import_json_database" method="post">
                    <input type="hidden" name="filename" value="<?= urlencode($db_filename) ?>" />
                    <p class="a_r"><a href='#' onclick="$('#form_import_json_database').submit();return false;" class="bt">
                            import the selected tables in the current database</a></p>
                    <p class="a_r">to populate the '_id_' field with existing 'id' field? 
                        <select name="populate_id"> 
                            <option value='yesyes' <?= empty($_POST['populate_id']) || $_POST['populate_id']=='yesyes' ? "selected='selected'" : "" ?>>
                                Yes, and remove 'id' field</option>
                            <option value='yesno' <?= !empty($_POST['populate_id']) && $_POST['populate_id']=='yesno' ? "selected='selected'" : "" ?>>
                                Yes, but don't remove 'id' field</option>
                            <option value='no' <?= !empty($_POST['populate_id']) && $_POST['populate_id']=='no' ? "selected='selected'" : "" ?>>
                                No</option>
                        </select>
                           </p>
                    <table border='0' cellpadding='5' align='right'>
                        <tr style="color:#ffffff;background:#000000;">
                            <td class="a_c n_w">
                                to import?
                                <br /><a href="#" style="color:yellow;" onclick="$('#form_import_json_database input[type=checkbox]').click();return false"><u>all</u></a>
                            </td>
                            <td>table name</td>
                            <td style='min-width:150px;'>fields_list</td>
                            <td class="a_c">#records</td>
                            <td class="a_c">size</td>
                        </tr>
                        <?php $ii=0; foreach($a_db['tables'] as $tb_name=>$table){ $ii++; ?>
                        <tr style="color:#000000; background:<?= ($ii % 2 == 0)?'#eaeaea' : '#f6f6f6' ?>" valign=top>
                            <td class="a_c">
                                <input type="checkbox" name="tables[<?= $tb_name ?>]" checked="checked" />
                            </td>
                            <td><?= $tb_name ?></td>
                            <td>
                                <a href='#' rel='0' id='a_json_db_<?= $tb_name ?>_field_list' onclick="js_toggle_field_list('json_db_<?= $tb_name ?>');
                                            return false;" style='line-height:25px;'>show fields</a> (<?= count($table['fields']) ?>)
                                <div style='display:none;' id='div_json_db_<?= $tb_name ?>_field_list'>
                                    <?= implode('<br />',$table['fields']) ?>
                                </div></td>
                            <td class="a_c"><?= number_format($table['n_records'],0,'.',',') ?></td>
                            <td class="a_c"><?= f_bytes_format($table['size']) ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                </form>
                <div class="clear"></div>
            </div>
            <?php } ?>
        </td>
    </tr>
    <?php }?>
    
</table>

<script>
        function js_toggle_field_list(itable) {
            var b_visible = document.getElementById('a_' + itable + '_field_list').rel;
            if (b_visible == '0') {
                document.getElementById('div_' + itable + '_field_list').style.display = 'block';
                document.getElementById('a_' + itable + '_field_list').rel = '1';
            } else {
                document.getElementById('div_' + itable + '_field_list').style.display = 'none';
                document.getElementById('a_' + itable + '_field_list').rel = '0';
            }
        }
</script>
