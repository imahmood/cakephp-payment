<?php
/**
 * @var string $redirectUrl
 * @var string $authority
 */

$this->setLayout('CakePayment.gateway');
$this->assign('pageTitle', 'انتقال به درگاه پرداخت آنلاین');
?>

<form name="form" id="form-gateway" method="GET" action="<?= $redirectUrl . $authority ?>"></form>

<script>
    setTimeout(function () {
        document.getElementById('form-gateway').submit();
    }, 300);
</script>
