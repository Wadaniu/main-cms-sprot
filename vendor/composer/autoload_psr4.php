<?php

// autoload_psr4.php @generated by Composer

$vendorDir = dirname(__DIR__);
$baseDir = dirname($vendorDir);

return array(
    'think\\view\\driver\\' => array($vendorDir . '/topthink/think-view/src'),
    'think\\trace\\' => array($vendorDir . '/topthink/think-trace/src'),
    'think\\captcha\\' => array($vendorDir . '/topthink/think-captcha/src'),
    'think\\app\\' => array($vendorDir . '/topthink/think-multi-app/src'),
    'think\\' => array($vendorDir . '/topthink/framework/src/think', $vendorDir . '/topthink/think-filesystem/src', $vendorDir . '/topthink/think-helper/src', $vendorDir . '/topthink/think-orm/src', $vendorDir . '/topthink/think-template/src'),
    'app\\' => array($baseDir . '/app'),
    'Symfony\\Polyfill\\Php80\\' => array($vendorDir . '/symfony/polyfill-php80'),
    'Symfony\\Polyfill\\Php72\\' => array($vendorDir . '/symfony/polyfill-php72'),
    'Symfony\\Polyfill\\Mbstring\\' => array($vendorDir . '/symfony/polyfill-mbstring'),
    'Symfony\\Component\\VarExporter\\' => array($vendorDir . '/symfony/var-exporter'),
    'Symfony\\Component\\VarDumper\\' => array($vendorDir . '/symfony/var-dumper'),
    'Psr\\SimpleCache\\' => array($vendorDir . '/psr/simple-cache/src'),
    'Psr\\Log\\' => array($vendorDir . '/psr/log/Psr/Log'),
    'Psr\\Http\\Message\\' => array($vendorDir . '/psr/http-message/src'),
    'Psr\\Container\\' => array($vendorDir . '/psr/container/src'),
    'PHPMailer\\PHPMailer\\' => array($vendorDir . '/phpmailer/phpmailer/src'),
    'League\\MimeTypeDetection\\' => array($vendorDir . '/league/mime-type-detection/src'),
    'League\\Flysystem\\' => array($vendorDir . '/league/flysystem/src'),
    'Firebase\\JWT\\' => array($vendorDir . '/firebase/php-jwt/src'),
);