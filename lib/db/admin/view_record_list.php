
<table border='0' cellspacing='20' align='center' style='border:none!important;'>
    <tr>
        <td>
            <b>
                [ <a href='index.php' style='font-weight:normal;'>back to the list of tables</a> ]
                <br /><br />
                Records of this table:
                <span style='color:#c40000;letter-spacing:0.2em;font-weight:normal;'> <?= $table['nombre'] ?></span> (<?= number_format(intval($table['total_records']),0,'.',',') ?>)
            </b>
            &nbsp; &nbsp; &nbsp; [ <a href="#" onclick="js_trim_asked(); return false;" title="Trim blank spaces that they appear usually when importing data from OLD database version">trim blank spaces</a> ]
            <script>
                function js_trim_asked(){
                    document.location = 'index.php?pag=records&op1=trim&table_name=<?= $table['nombre'] ?>';
                }
                $(document).ready(function(){
                    $('#input_current_page').bind('change',function(){
                        if ($(this).val()=='') return;
                        document.location = 'index.php?pag=records&table_name=<?= $table['nombre'] ?>&k_order=<?= $k_order ?>&i_page='+$(this).val();
                    });
                });
            </script>
            <?= _var_export($_POST) ?>
        </td>
    </tr>
    
    <tr>
        <td valign='top'>		
            <table id='content'>
                <tr>
                    <td valign='top'>
                        
                        <!-- ====== ====== ====== SEARCH DIALOG ====== ====== ====== -->
                        <form method="post" id="search_form" action="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=1&k_order=<?= $k_order ?>">
                            <table border='0' cellpadding='5' style='width:100%;<?= (isset($config['search_query']) && $config['search_query']!='')?"outline:2px #00c solid;":"" ?>'>
                                <thead>
                                    <tr>
                                        <td colspan='2'>
                                            <div style="position:relative;">
                                            <b>Search</b>
                                                <?php if (isset($config['search_query']) && $config['search_query']!=''){ ?>
                                                - filtered records: <b><?= $table['filtered_records'] ?></b>
                                                (<?= number_format(($table['filtered_records']*100/$table['total_records']),1,'.',',')?>%)
                                                <a href='index.php?op1=export_table_csv&filter=1&table_name=<?= $table['nombre'] ?>' title='Export filtered records in a CSV file' style="position:absolute;top:0px;right:0px;"><img src='imagenes/b_csv.png' border=0></a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr valign=top>
                                        <td class='a_r'>Query:</td>
                                        <td>
                                            <input type='text' name='search_query' id='search_query' value="<?= (isset($config['search_query']))? htmlspecialchars($config['search_query']):'' ?>" style='width:100%' />
                                            <br /><input type='checkbox' name='search_regexp' <?= (isset($config['search_regexp']) && $config['search_regexp']=='on')? "checked='true'":'' ?> />
                                            <em>use it as regular expression<br />(use \ as ESCAPE char, and / as DELIMITER char)</em>
                                        </td>
                                    </tr>
                                    <tr valign=top>
                                        <td class='a_r'>Field:</td>
                                        <td>
                                            <select name='search_field' class='a_c' style='width:100%'>
                                                <option value='*' <?= (!isset($config['search_field']) || $config['search_field']=='*')?"selected='selected'":"" ?>> -- all fields --</option>
                                                <option value='_id_' <?= (!empty($config['search_field']) && $config['search_field']=='_id_')?"selected='selected'":"" ?>>id</option>
                                                <?php foreach ($table['lista_campos'] as $campo) { ?>    
                                                <option value="<?= htmlspecialchars($campo) ?>" <?= (!empty($config['search_field']) && $config['search_field']==$campo)?"selected='selected'":"" ?>><?= $campo ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr valign=top>
                                        <td class='a_r'>&nbsp;</td>
                                        <td class='a_r'>
                                            <input type='button' value="Search" onclick="$('#search_form').submit();">
                                            <?php if (isset($_COOKIE['tb_'.$table['nombre'].'_search_query'])){ ?>
                                            <input type='button' value="Reset" onclick="$('#search_query').val('');$('#search_form').submit();">
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>		
                        <br />
                        <!-- ====== ====== ====== EDIT/ADD RECORD ====== ====== ====== -->
                        <form method="post" id="form1" action="index.php">
                            <input type='hidden' name='pag' value='records'>
                            <input type='hidden' name='table_name' value='<?= $table['nombre'] ?>'>
                            <input type='hidden' name='op1' value="save_record">
                            <input type='hidden' name='id_record' value="<?= (!empty($_REQUEST['id_record']) && !empty($_REQUEST['op1']) && $_REQUEST['op1']=='edit_record') ? $_REQUEST['id_record'] : '' ?>">
                            <table border=0 cellpadding=5>
                                <thead>
                                    <tr>
                                        <?php if ($op1 == 'edit_record'){ ?>
                                            <td>Edit <b>record #<?= $_REQUEST['id_record'] ?></b></td>
                                        <?php }else{ ?>
                                            <td>Add a <b>new record</b></td>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr valign=top>
                                        <td>
                                            <?php
                                            foreach ($table['lista_campos'] as $campo) {
                                            if (trim($campo) != ""){
                                            ?>    
                                                <br /><?= $campo ?>:
                                                <?php if (!empty($editable_record[$campo]) && is_array(@unserialize($editable_record[$campo]))){ ?>
                                                &nbsp; <a href='#' onclick="$(this).next().toggle();return false;">(see unserialized)</a>
                                                <div style='display:none;'><?= _var_export(unserialize($editable_record[$campo])) ?></div> 
                                                <?php    } ?>
                                                <br>
                                                <textarea name='f_<?= $campo ?>' style='width:300px;' rows='1'
                                                          ><?= isset($editable_record[$campo]) ? $editable_record[$campo] : '' ?></textarea>
                                                <br />
                                            <?php }} ?>

                                            <br /><input type='button' value="<?= ($op1 == 'edit_record') ? 'Save changes':'Add record' ?>"
                                                         onclick="document.getElementById('form1').submit();"><br />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>		
                        
                    </td>
                    <td style='' valign='top'>
                        
                        <!-- ====== ====== ====== PAGINATION OF RESULTS ====== ====== ====== -->
                        <table border='0' cellpadding='5' width='100%' id='tb_paginado'>
                            <thead>
                                <tr>
                                    <td style='width:30px;'><b>Pages</b></td><td>
                                        <?php $block_size = 5; ?>
                                        
                                        <?php if ($i_page < ($block_size + 4)){ ?>
                                        
                                        <!-- first 5 ($block_size) pages -->
                                            <?php for ($ii = 1; $ii < $i_page; $ii++) { ?>
                                                <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=<?= $ii ?>&k_order=<?= $k_order ?>" 
                                                    title="page <?= $ii ?>" class="<?= ($ii == $i_page) ? 'page_selected' : 'page_clickable' ?>"
                                                 ><?= $ii ?></a>
                                            <?php } ?>

                                        <?php }else{ ?>
                                        
                                            <?php for ($ii = 1; $ii < $block_size; $ii++) { ?>
                                                <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=<?= $ii ?>&k_order=<?= $k_order ?>" 
                                                    title="page <?= $ii ?>" class="<?= ($ii == $i_page) ? 'page_selected' : 'page_clickable' ?>"
                                                 ><?= $ii ?></a>
                                            <?php } ?>
                                            <span color="white">...</span>
                                        
                                        <!-- 2 pages at left of current page -->
                                            <?php $ii = $i_page - 2; ?>
                                            <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=<?= $ii ?>&k_order=<?= $k_order ?>" 
                                                title="page <?= $ii ?>" class="<?= ($ii == $i_page) ? 'page_selected' : 'page_clickable' ?>"
                                             ><?= $ii ?></a>
                                            <?php $ii = $i_page - 1; ?>
                                            <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=<?= $ii ?>&k_order=<?= $k_order ?>" 
                                                title="page <?= $ii ?>" class="<?= ($ii == $i_page) ? 'page_selected' : 'page_clickable' ?>"
                                             ><?= $ii ?></a>

                                        <?php } ?>
                                            
                                        <!-- current page (as input type=text) -->
                                             <input id="input_current_page" type="text" class="a_c" style="width:50px;border-radius:3px;" value="<?= $i_page ?>" />
                                             
                                        <?php if ($i_page > ($n_pages - $block_size - 4)){ ?>
                                             
                                            <?php for ($ii = ($i_page + 1); $ii < ($n_pages + 1); $ii++) { ?>
                                                <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=<?= $ii ?>&k_order=<?= $k_order ?>" 
                                                    title="page <?= $ii ?>" class="<?= ($ii == $i_page) ? 'page_selected' : 'page_clickable' ?>"
                                                 ><?= $ii ?></a>
                                            <?php } ?>
                                             
                                        <?php }else{ ?>
                                             
                                        <!-- 2 pages at right of current page -->
                                            <?php $ii = $i_page + 1; ?>
                                            <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=<?= $ii ?>&k_order=<?= $k_order ?>" 
                                                title="page <?= $ii ?>" class="<?= ($ii == $i_page) ? 'page_selected' : 'page_clickable' ?>"
                                             ><?= $ii ?></a>
                                            <?php $ii = $i_page + 2; ?>
                                            <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=<?= $ii ?>&k_order=<?= $k_order ?>" 
                                                title="page <?= $ii ?>" class="<?= ($ii == $i_page) ? 'page_selected' : 'page_clickable' ?>"
                                             ><?= $ii ?></a>

                                             <span color="white">...</span>
                                             
                                        <!-- last 5 ($block_size) pages -->
                                            <?php for ($ii = ($n_pages - $block_size +2); $ii < ($n_pages + 1); $ii++) { ?>
                                                <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=<?= $ii ?>&k_order=<?= $k_order ?>" 
                                                    title="page <?= $ii ?>" class="<?= ($ii == $i_page) ? 'page_selected' : 'page_clickable' ?>"
                                                 ><?= $ii ?></a>
                                            <?php } ?>
                                        
                                        <?php } ?>
                                        
                                    </td>
                                    <td style='width:30px;'>
                                        <form id='form_ele_per_page' action="index.php?pag=records&table_name=<?= $table['nombre'] ?>&i_page=1&k_order=<?= $k_order ?>" method='post'>
                                            <b>Ele/Page</b><br /> 
                                            <select name='records_per_page' class='a_c' onchange="$('#form_ele_per_page').submit();">
                                                <?php for($i=50;$i<1001;$i+=50){ ?>
                                                <option value='<?= $i ?>' <?= ($i==$config['records_per_page'])? "selected='selected'":"" ?>><?= $i ?></option>
                                                <?php } ?>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                        
                        
                        <!-- ====== ====== ====== RESULTS TABLE ====== ====== ====== -->
                        <style>.tb_records td{vertical-align:middle;}</style>
                        <table class='tb_records' border='0' cellpadding='5'>
                            <thead>
                                <tr>

                                    <?php if (empty($k_order) || $k_order == '_id_') { ?>
                                        <td>id</td>
                                    <?php }else{ ?>
                                        <td><a style='text-decoration:underline;' href='index.php?pag=records&table_name=<?= $table['nombre'] 
                                            ?>&i_page=1&k_order=_id_'>id</a></td>
                                    <?php } ?>

                                    <?php foreach ($table['lista_campos'] as $campo) { ?>

                                        <?php if (!empty($k_order) && $k_order == $campo) { ?>
                                            <td><?= $campo ?></td>
                                        <?php }else{ ?>
                                            <td><a style='text-decoration:underline;' href='index.php?pag=records&table_name=<?= $table['nombre'] 
                                                ?>&i_page=1&k_order=<?= $campo ?>'><?= $campo ?></a></td>
                                        <?php } ?>

                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                               if (count($records) > 0) {
                                   $ii = 0;
                                   foreach ($records as $record) { $ii++;
                            ?>
                               <tr class="<?= ($ii % 2 == 0)?'tr_pair' : 'tr_odd' ?>" valign=top>
                                   <td style='text-align:right;'>
                                       <table border=0 width=100%>
                                           <tr>
                                               <td>
                                                   <a href='index.php?pag=records&table_name=<?= $table['nombre'] 
                                                       ?>&op1=edit_record&id_record=<?= $record['_id_'] ?>'><?= $record['_id_'] ?></a>
                                               </td>
                                               <td width=5px>
                                                   <a href='index.php?pag=records&table_name=<?= $table['nombre'] 
                                                       ?>&op1=edit_record&id_record=<?= $record['_id_'] ?>' title="edit">
                                                       <img src="imagenes/b_edit.png" border=0>
                                                   </a>
                                               </td>
                                               <td width=5px><a href='index.php?pag=records&table_name=<?= $table['nombre'] 
                                                                   ?>&i_page=<?= $i_page ?>&op1=delete_record&id_record=<?= $record['_id_'] ?>' 
                                                                   onclick="if (!confirm('Please, confirm that you want to delete this record.')) { return false;}" 
                                                                   title="delete"><img src="imagenes/b_deltbl.png" border=0></a>
                                               </td>
                                           </tr>
                                       </table>
                                   </td>

                                   <?php 
                                       foreach ($table['lista_campos'] as $campo) { 
                                       if (trim($campo) != "") { 
                                           $v = $record[trim($campo)];
                                           $txt = (strlen($v) < $config_char_size) ? $v : mb_substr($v, 0, $config_char_size - 3, 'UTF-8') . "...";
                                           $txt8 = htmlentities($txt, ENT_COMPAT, 'UTF-8');
                                   ?>    
                                           <td><?= !empty($txt8) ? $txt8 : $txt ?></td>

                                   <?php }} ?>

                               </tr>

                               <?php }} ?>
                            </tbody>
                        </table>					
                    </td>
                </tr>
            </table>
            
        </td>
    </tr>
</table>
