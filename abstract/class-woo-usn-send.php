<?php 

namespace Homescriptone\USN;

abstract class Send{

    abstract function send( $gateway_type, $to_number, $message, $media_url );

    abstract function decode_response( $gateway,  $to_decode );
}