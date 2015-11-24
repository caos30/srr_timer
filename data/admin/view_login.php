<div id='content' style='width:400px;padding:41px;margin:71px auto;'>

    <form method="post" name="form1" id="form1" action="index.php">
                    <input type="hidden" name="pag" value="validate_login">
                    <table width="300" id="tb_login" border="0" cellspacing="10" cellpadding="0">
                        <tr> 
                            <td width="200" align=right><font class=label_form>user</font></td>
                            <td width="275">
                                <input name="username" id="username" type="text" size="20" 
                                             onkeyup="javascript:if (getKeyCode(event) == 13) document.getElementById('password').focus();"> 
                            </td>
                        </tr>
                        <tr> 
                            <td width="200" align=right><font class=label_form>password</font></td>
                            <td width="275">
                                <input name="passwd" id="passwd" type="password" size="20" 
                                       onkeyup="javascript:if (getKeyCode(event) == 13) document.getElementById('form1').submit();"> 
                            </td>
                        </tr>
                        <tr> 
                            <td width="200" align=right></td>
                            <td width="275">
                                <input value=" log in " type="button" width="40" onclick="document.form1.submit();"> 
                            </td>
                        </tr>
                    </table>
    </form>
    <script>
        function getKeyCode(e) {
        e = (window.event) ? event : e;
        intKey = (e.keyCode) ? e.keyCode : e.charCode;
        return intKey;
        }
        document.getElementById('username').focus();
    </script>
</div>
