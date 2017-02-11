<table border='0' cellspacing='20' align='center' style='border:none;min-width:70%;'>
    <tr>
        <td>
            <b>
                [ <a href='index.php' style='font-weight:normal;'>back to the list of tables</a> ]
                <br /><br />
                Statistics of this table:
                <select style='padding:3px 5px;' onchange="document.location='index.php?pag=statistics&table_name='+$(this).val();">
                    <?php foreach($tables as $table_name=>$arr){ ?>
                    <option value='<?= $table_name?>' <?= $table['nombre']==$table_name ? "selected='selected'":"" ?>>
                            <?= $table_name?> &nbsp; &nbsp; (<?= number_format($arr['num_records'],0,'.',',') ?> records)
                    </option> 
                    <?php } ?>
                </select>
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
        </td>
    </tr>
    
    <tr>
        <td valign='top'>		
            <table id='content' style="position:relative;width:100%;">
                <tr>
                    <td style='text-align:center;'>
                        <script>
                            function js_maximize(div_id){
                                if ($('#'+div_id).hasClass('maximized')){
                                    var data_width = $('#'+div_id).attr('data-width');
                                    var data_height = $('#'+div_id).attr('data-height');
                                    $('#'+div_id).removeClass('maximized').css('width',data_width).css('height',data_height);
                                    $('#'+div_id+' .ic-arrow').html('&#8689;');
                                }else{
                                    var content_height = Math.max($('#content').outerHeight(), $(window).outerHeight()-270);
                                    $('#content').css('height',content_height+'px');
                                    var data_width = $('#'+div_id).outerWidth() + 'px'; 
                                    var data_height = $('#'+div_id).outerHeight() + 'px'; 
                                    $('#'+div_id)   .addClass('maximized')
                                                    .css('width',($('#content').outerWidth() -30) +'px')
                                                    .css('height',( content_height -30) +'px')
                                                    .attr('data-width',data_width)
                                                    .attr('data-height',data_height)
                                                    ;
                                    $('#'+div_id+' .ic-arrow').html('&#8690;');
                                }
                            }
                        </script>
                        <?php 
                            foreach ($statistics as $ftit=>$arr){ 
                                if ($ftit=='_id_') continue;
                        ?>
                        <!-- ====== ====== ====== RESULTS TABLE ====== ====== ====== -->
                        <div id='statistics_<?= $ftit ?>' style='display:inline-block;width:250px;height:200px;margin:1rem;overflow:auto;'>
                            <table border='0' cellpadding='5' style='width:100%;'>
                                <thead>
                                    <tr>
                                        <td class='a_l'>
                                            <a href='#' style='display:block;' onclick="js_maximize('statistics_<?= $ftit ?>');return false;">
                                                <span class='ic-arrow'>&#8689;</span>
                                            <?= $ftit ?></a>
                                        </td>
                                        <td style='width:20px;white-space:nowrap;text-align: right;'><?= number_format($arr['n'],0,'.',',') ?></td>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                    $ii=0;
                                    if (count($arr['stats']) > 0) { $ii++;
                                       foreach ($arr['stats'] as $v=>$n) {
                                ?>
                                   <tr class="<?= ($ii % 2 == 0)?'tr_pair' : 'tr_odd' ?>">
                                       <td class='a_l'>
                                        <a href="index.php?pag=records&table_name=<?= $table['nombre'] ?>&search_field=<?= $ftit?>&search_query=<?= urlencode($v) ?>" title="List records">
                                            <img src="imagenes/b_props.png" style='border:none;margin-bottom:-3px;'>
                                            <?= htmlspecialchars($v) ?>
                                        </a>
                                       </td>
                                       <td style='text-align:right;'><?= number_format($n,0,'.',',') ?></td>
                                   </tr>

                                   <?php }} ?>
                                </tbody>
                            </table>	
                        </div>
                        
                        <?php } ?>
                        
                    </td>
                </tr>
            </table>
            
        </td>
    </tr>
</table>
