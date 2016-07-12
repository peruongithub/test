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

    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

    <style type="text/css" title="currentStyle">
        input {
            margin: 5px 0px;
        }

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

                <form class="form-signin" action="<?php echo $url; ?>" method="<?php echo $method; ?>">
                    <input name="login" type="text" class="form-control" placeholder="Login" required autofocus
                           value="<?php echo $login; ?>">
                    <input name="email" type="email" class="form-control" placeholder="Email" required
                           value="<?php echo $email; ?>">
                    <input name="name" type="text" class="form-control" placeholder="You name"
                           value="<?php echo $name; ?>">
                    <input name="birthday" type='text' class="form-control" placeholder=""
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
                    <input name="password" type="password" class="form-control" placeholder="Password" required>
                    <input name="confirm_password" type="password" class="form-control" placeholder="Confirm password"
                           required>
                    <input type="submit" class="btn btn-lg btn-primary btn-block" value="Sign in">
                    <footer class="clearfix">
                        <label for="agree" class="checkbox-inline"><input id="agree" name="agree" type="checkbox">I
                            agree
                            with terms and conditions</label>
                    </footer>

                </form>

            </div>
            <h1><?php echo $errors; ?></h1>
            <script type="text/javascript">
                $(document).ready(function () {
                    $.fn.selectpicker.defaults = {width: 'css-width'};

                    $('input[name=birthday]').datetimepicker({
                        format: 'DD MMMM Y'
                    });
                    /*
                     $('input[name=login]').onblur(function () {
                     $.getJSON("index.php?r=user&a=delete&messageID=" + messageID + " ", function (data) {
                     $.showmessage({
                     type: 'success',
                     message: '' + $.NotCachedVars.RemoveSuccessText + ''
                     });
                     });
                     });
                     */

                });
            </script>
        </div>
    </div>
</div>
</body>
</html>