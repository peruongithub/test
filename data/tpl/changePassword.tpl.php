<div class="row">
    <div class="col-md-12">
        <h2 class="form-signin-heading">Change Password</h2>

        <form id="password-form" class="form-signin" action="<?php echo $uri; ?>" method="<?php echo $method; ?>">
            <div class="form-group">
                <div class="inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-sunglasses"></i></span>
                        <input name="old_password" type="password" class="form-control"
                               placeholder="Enter you old password" required autofocus>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-sunglasses"></i></span>
                        <input name="password" type="password" class="form-control" placeholder="Password"
                               required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-sunglasses"></i></span>
                        <input name="confirm_password" type="password" class="form-control"
                               placeholder="Confirm password"
                               required>
                    </div>
                </div>
            </div>
            <input id="submit_password" type="button" class="btn btn-lg btn-primary btn-block" value="Change">
        </form>
        <script type="text/javascript">
            $(document).ready(function () {
                var changePassword = $('form#password-form');
                changePassword.bootstrapValidator({
                    // To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {

                        password: {
                            validators: {
                                stringLength: {
                                    min: <?php echo $minPassLen; ?>
                                },
                                identical: {
                                    field: 'confirm_password',
                                    message: 'The password and its confirm are not the same'
                                },
                                notEmpty: {
                                    message: 'Please supply your password'
                                }
                            }
                        },
                        confirm_password: {
                            validators: {
                                stringLength: {
                                    min: <?php echo $minPassLen; ?>
                                },
                                identical: {
                                    field: 'password',
                                    message: 'The password and its confirm are not the same'
                                },
                                notEmpty: {
                                    message: 'Please supply your password'
                                }
                            }
                        }
                    }
                });

                $('input#submit_password').click(function () {
                    changePassword.ajaxSubmit({
                        dataType: 'json',
                        resetForm: true,
                        success: function (json, statusText, xhr, form) {
                            changePassword.data('bootstrapValidator').resetForm();
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