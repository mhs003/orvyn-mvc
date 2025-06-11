<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 1 */ ?>
<?php $this->useLayout('base'); ?>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 2 */ ?>

<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 3 */ ?>
<?php $this->startSection('content'); ?>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 4 */ ?>
    Welcome to Orvyn! <br><br>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 5 */ ?>
    <form method='post' action='<?php echo htmlspecialchars( route('store') , ENT_QUOTES); ?>'>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 6 */ ?>
        <input name='name' placeholder='Enter your name' />
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 7 */ ?>
        <button type='submit'>Submit</button>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 8 */ ?>
    </form><br>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 9 */ ?>
    To <a href='<?php echo htmlspecialchars( route('test', ['param' => 'test']) , ENT_QUOTES); ?>'>test page</a><br>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 10 */ ?>
    <?php echo ("ooTest"); ?><br>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 11 */ ?>
<?php $this->endSection(); ?>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 12 */ ?>

<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 13 */ ?>
<?php $this->startSection('script'); ?>
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 14 */ ?>
    adfasdg
<?php /* Template: /srv/http/orvyn.test/bootstrap/../resource/views/welcome.php, Line: 15 */ ?>
<?php $this->endSection(); ?>
