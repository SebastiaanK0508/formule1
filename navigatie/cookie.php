<?php
if (isset($_GET['action']) && $_GET['action'] === 'accept') {
    $type = isset($_GET['type']) ? $_GET['type'] : 'essential';
    setcookie('f1_consent', $type, time() + (365 * 24 * 60 * 60), "/");
    exit; 
}
?>