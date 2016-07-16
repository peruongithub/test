<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Registration</title>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="/data/MessageBoxes/messageboxes.css" type="text/css"/>
    <script src="/data/MessageBoxes/jquery.messageboxes.js"></script>

    <!-- bootstrap-datetimepicker -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.14.1/moment.min.js"></script>
    <script type="text/javascript"
            src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet"
          href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css"/>

    <!-- bootstrap-select -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

    <!-- bootstrap-validator -->
    <link rel="stylesheet"
          href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>


    <style type="text/css" title="currentStyle">


        .container {
            width: 400px;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="login-form">
                <h2 class="form-signin-heading">Авторизация на сайте</h2>

                <form id="register_form" class="form-signin" action="<?php echo $url; ?>"
                      method="<?php echo $method; ?>" data-bv-feedbackicons-valid="glyphicon glyphicon-ok"
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

                    <input type="submit" class="btn btn-lg btn-primary btn-block" value="Sign in">
                    <footer class="clearfix">
                        <label for="agree" class="checkbox-inline"><input id="agree" name="agree" type="checkbox">I
                            agree
                            with terms and conditions</label>
                    </footer>

                </form>

            </div>
            <script type="text/javascript">
                $(document).ready(function () {
                    $.fn.selectpicker.defaults = {width: 'css-width'};

                    $('input[name=birthday]').datetimepicker({
                        format: 'DD MMMM Y'
                    });

                    $('#register_form').bootstrapValidator({
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

                });
            </script>
        </div>
    </div>
</div>
</body>
</html>