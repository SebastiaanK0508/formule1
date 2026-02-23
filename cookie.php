<?php
if (isset($_GET['action']) && $_GET['action'] === 'accept') {
    setcookie('f1_consent', 'true', time() + (86400 * 30), "/");
    exit('Success');
}
?>