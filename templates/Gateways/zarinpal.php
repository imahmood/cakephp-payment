<?php
$this->layout = 'CakePayment.gateway';
?>

<!DOCTYPE html>
<html>

<head>
</head>

<body>
    <form name="form" id="arina-gateway" method="GET" action="<?= $redirectUrl . $authority ?>"></form>

    <script>
        setTimeout(function() {
            document.getElementById('arina-gateway').submit();
        }, 300);
    </script>
</body>

</html>
