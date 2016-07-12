<div class="row">
    <div class="col-md-12">
        <h2 class="form-signin-heading">Change Password</h2>

        <form id="password-form" class="form-signin" action="<?php echo $uri; ?>" method="<?php echo $method; ?>">
            <input name="old_password" type="password" class="form-control"
                   placeholder="Enter you old password" required autofocus>
            <input name="password" type="password" class="form-control"
                   placeholder="Password" required>
            <input name="confirm_password" type="password" class="form-control"
                   placeholder="Confirm you new password" required>
            <input id="submit_password" type="button" class="btn btn-lg btn-primary btn-block" value="Change">
        </form>
        <script type="text/javascript">
            $(document).ready(function () {
                $('input#submit_password').click(function () {
                    $('form#password-form').ajaxSubmit({
                        dataType: 'json',
                        resetForm: true,
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