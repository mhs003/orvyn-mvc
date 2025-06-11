<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/containers/base.php, Line: 1 */ ?>
<html>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/containers/base.php, Line: 2 */ ?>
<body>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/containers/base.php, Line: 3 */ ?>
    <h2>Current time is: <?php echo date('Y-m-d H:i:s'); ?></h2>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/containers/base.php, Line: 4 */ ?>
    <div style="border: 1px solid red; background-color: aquamarine; padding: 10px; border-radius: 10px;">
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/containers/base.php, Line: 5 */ ?>
        <?php echo $this->yieldContent('content'); ?>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/containers/base.php, Line: 6 */ ?>
    </div>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/containers/base.php, Line: 7 */ ?>
</body>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/containers/base.php, Line: 8 */ ?>
</html>
