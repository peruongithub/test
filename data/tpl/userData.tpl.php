<div class="row">
    <div class="col-md-12">
        <h2 class="form-signin-heading">Personal info</h2>
        <form id="personalInfo" class="form-signin" action="<?php echo $uri; ?>" method="<?php echo $method; ?>">
            <input name="login" type="text" class="form-control" placeholder="Login" required autofocus
                   value="<?php echo $login; ?>">
            <input name="email" type="email" class="form-control" placeholder="Email" required
                   value="<?php echo $email; ?>">
            <input name="name" type="text" class="form-control" placeholder="You name" value="<?php echo $name; ?>">
            <input name="birthday" type="datetime" class="form-control" placeholder=""
                   value="<?php echo $birthday; ?>">

            <select name="country" class="form-control selectpicker">
                <?php
                foreach ($country_list as $row) {
                    echo '<option value="'.$row['id'].'" name="'.$row['code'].'"'.
                        (
                        ($country_id > 0 && $row['id'] === $country_id)
                        ||
                        $row['code'] == $country_code
                            ? ' selected' : '').'>'.$row['name'].'</option>';
                }
                ?>
            </select>

            <input id="submit_personalInfo" type="button" class="btn btn-lg btn-primary btn-block" value="Update">
        </form>

        <script type="text/javascript">
            $(document).ready(function () {
                $.fn.selectpicker.defaults = {width: 'css-width'};

                $('input[name=birthday]').datetimepicker({
                    format: 'DD MMMM Y'
                });

                $('input#submit_personalInfo').click(function () {
                    $('form#personalInfo').ajaxSubmit({
                        dataType: 'json',
                        resetForm: false,
                        success: function (json, statusText, xhr, form) {
                            $.showmessage({
                                type: 'success',
                                message: json.message
                            });
                        }
                    });
                });
            });
        </script>
    </div>
</div>