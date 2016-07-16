<div class="row">
    <div class="col-md-12">
        <h2 class="form-signin-heading">Personal info</h2>
        <form id="personalInfo" class="form-signin" action="<?php echo $uri; ?>" method="<?php echo $method; ?>" data-bv-feedbackicons-valid="glyphicon glyphicon-ok"
              data-bv-feedbackicons-invalid="glyphicon glyphicon-remove"
              data-bv-feedbackicons-validating="glyphicon glyphicon-refresh" data-bv-live="enabled">
            <!-- login -->
            <div class="form-group">
                <div class="inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input name="login" type="text" class="form-control" placeholder="Login" required
                               autofocus
                               value="<?php echo $login; ?>">
                    </div>
                </div>
            </div>
            <!-- email -->
            <div class="form-group">
                <div class="inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                        <input name="email" type="email" class="form-control" placeholder="Email" required
                               value="<?php echo $email; ?>">
                    </div>
                </div>
            </div>
            <!-- name -->
            <div class="form-group">
                <div class="inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input name="name" type="text" class="form-control" placeholder="You name"
                               value="<?php echo $name; ?>">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        <input name="birthday" type='text' class="form-control" placeholder=""
                               value="<?php echo $birthday; ?>">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-list"></i></span>
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
                    </div>
                </div>
            </div>

            <input id="submit_personalInfo" type="button" class="btn btn-lg btn-primary btn-block" value="Update">
        </form>

        <script type="text/javascript">
            $(document).ready(function () {
                $.fn.selectpicker.defaults = {width: 'css-width'};

                $('input[name=birthday]').datetimepicker({
                    format: 'DD MMMM Y'
                });

                var personalInfo = $('form#personalInfo');
                personalInfo.bootstrapValidator({
                    // To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        login: {
                            validators: {
                                stringLength: {
                                    min: 4,
                                },
                                notEmpty: {
                                    message: 'Please supply your login'
                                },
                                remote: {
                                    message: 'The username is not available',
                                    url: '<?php echo $checkUniqueLogin; ?>'
                                }
                            }
                        },
                        email: {
                            validators: {
                                notEmpty: {
                                    message: 'Please supply your email address'
                                },
                                emailAddress: {
                                    message: 'Please supply a valid email address'
                                },
                                remote: {
                                    message: 'The email is not available',
                                    url: '<?php echo $checkUniqueEmail; ?>'
                                }
                            }
                        },
                        birthday: {
                            validators: {
                                notEmpty: {
                                    message: 'Please supply your birthday'
                                }
                            }
                        },
                        name: {
                            validators: {
                                stringLength: {
                                    min: 4
                                },
                                notEmpty: {
                                    message: 'Please supply your name'
                                }
                            }
                        },
                        country: {
                            validators: {
                                notEmpty: {
                                    message: 'Please select your country'
                                }
                            }
                        }
                    }
                });

                $('input#submit_personalInfo').click(function () {
                    personalInfo.ajaxSubmit({
                        dataType: 'json',
                        resetForm: false,
                        success: function (json, statusText, xhr, form) {
                            personalInfo.data('bootstrapValidator').resetForm();
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