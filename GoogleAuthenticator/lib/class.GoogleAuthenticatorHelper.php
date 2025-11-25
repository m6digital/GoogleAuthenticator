<?php
namespace GoogleAuthenticatorModule;

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

class GoogleAuthenticatorHelper
{
    public static function generateSecret()
    {
        $g = new GoogleAuthenticator();
        return $g->generateSecret();
    }

    public static function getQRCodeGoogleUrl($user, $secret, $title = 'CMSMS')
    {
        $g = new GoogleAuthenticator();
        return $g->getURL($user, $title, $secret);
    }

    public static function verifyCode($secret, $code)
    {
        $g = new GoogleAuthenticator();
        return $g->checkCode($secret, $code);
    }
}
