<?php

/*

    Encryption and Decryption class for Saguaro.
    Not audited for security, but probably not the worst thing in the world and better than before.

    Intended for legacy/pre-PHP 5.3+ & 7.
    If supported and available use the other SaguaroCrypt class, SaguaroCryptModern, which utilizes 5.3+ functions.

    Key stretching is not to be self-implemented without a standard supporting library (which may replace this).


    TESTING:

    $now = microtime(); //Basic profiler.
    $s = new SaguaroCryptLegacy;
    $test_key = 'ayelmao';
    $hash = $s->generate_hash($test_key);

    var_dump($s->testAlgorithms());
    var_dump($hash);
    echo ($s->compare_hash($test_key, $hash['hash'], $hash['public_salt'])) ? 't' : 'f';
    echo sprintf("<br>Took %f", microtime() - $now) . " seconds.";

*/

class SaguaroCryptLegacy {
    private $public_salt = ""; //Ironic.
    private $strong_form = ""; //Cache.

    function compare_hash($input, $hash, $hash_salt) {
        /*
            $input - Unencrypted string to hash with $hash_salt and compare to $hash.
        */
        return $this->slow_compare($this->createHash($input, $hash_salt), $hash);
    }

    private function slow_compare($a, $b) {
        //Timing attacks when?

        $diff = strlen($a) ^ strlen($b);

        for($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $diff === 0;
    }

    function generate_hash($input) {
        $this->generate_public();

        return [
            'public_salt' => $this->public_salt,
            'hash' => $this->createHash($input, $this->public_salt)
        ];
    }

    function generate_public() {
        $this->public_salt = $this->openssl_salt(); //Currently only implemented salt generation.

        return $this->public_salt;
    }

    function openssl_salt($len = 128) {
        $strong = true;
        $bytes = openssl_random_pseudo_bytes($len, $strong);

        return bin2hex($bytes);
    }

    function createHash($input, $salt) {
        $truesalt = str_replace('PJSALT', $salt, $this->getStrongAlgorithm());
        return crypt($input, $truesalt);
    }

    function getStrongAlgorithm() {
        //Use the 'strongest' (regardless of parameters supplied) algorithm available.

        if (!empty($this->strong_form))
            return $this->strong_form; //Return early if cached.

        foreach ($this->testAlgorithms() as $alg) {
            if ($alg['available'] === true) {
                $this->strong_form = $alg['form'];
                return $this->strong_form;
            }
        }
    }

    function testAlgorithms() {
        return [
            'CRYPT_SHA512' => [
                    'available' => (bool) CRYPT_SHA512,
                    'form' => '$6$rounds=120000$PJSALT$'
                ],
            'CRYPT_SHA256' => [
                    'available' => (bool) CRYPT_SHA256,
                    'form' => '$5$rounds=120000$PJSALT$'
                ],
            'CRYPT_BLOWFISH' => [
                    'available' => (bool) CRYPT_BLOWFISH,
                    'form' => '$2a$07$PJSALT$'
                ],
            'CRYPT_MD5' => [
                    'available' => (bool) CRYPT_MD5,
                    'form' => '$1$PJSALT$'
                ],
            'CRYPT_EXT_DES' => [
                    'available' => (bool) CRYPT_EXT_DES,
                    'form' => '_J9..PJSALT'
                ],
            'CRYPT_STD_DES' => [
                    'available' => (bool) CRYPT_STD_DES,
                    'form' => 'PJSALT'
                ]
        ];
    }
}

?>