<?php
echo $msg;

?>
<form action="resetpass" method="post">
    <table>
    <tr>
        <td>nueva contraseña</td>
        <td><input type="password" name="password" value="" /></td>
        <td><?php echo form_error('password'); ?></td>
    </tr>
    <tr>
        <td>confirmacion de contraseña</td>
        <td><input type="password" name="password2" value="" /></td>
        <td><?php echo form_error('password2'); ?></td>
    </tr>
    <tr>
        <td>
        <input type="hidden" name="token" value="<?php echo $token ?>" />
        <input type="hidden" name="email" value="<?php echo $email ?>" />
        &nbsp;</td>
        <td><input type="submit" value="reset" name="submit" /></td>
        <td>&nbsp;</td>
    </tr>
    </table>
</form>