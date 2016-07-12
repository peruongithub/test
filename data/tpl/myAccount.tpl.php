<div class="row">
    <div class="col-md-3">
        <!-- bootstrap-datetimepicker -->
        <script type="text/javascript" src="/bower_components/moment/min/moment.min.js"></script>

        <script type="text/javascript"
                src="/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        <link rel="stylesheet"
              href="/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css"/>

        <link rel="stylesheet" href="/bower_components/bootstrap-select/dist/css/bootstrap-select.min.css">
        <script src="/bower_components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

        <style type="text/css" title="currentStyle">
            input {
                margin: 5px 0px;
            }
        </style>
        <?php echo $myPersonalData; ?>
        <?php echo $changePassword; ?>
    </div>
    <div class="col-md-9">
        <?php echo $myData; ?>
    </div>
</div>
    