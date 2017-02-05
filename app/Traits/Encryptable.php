<?php

namespace App\Traits;
use Illuminate\Contracts\Encryption\DecryptException;
trait Encryptable {

    public function getAttribute($key) {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable)) {
            try {
                $decrypted = decrypt($value);
                return $decrypted;
            } catch (DecryptException $e) {
                return "";
            }
        }
        return $value;
        
    }

    public function setAttribute($key, $value) {
        if (in_array($key, $this->encryptable)) {
            $value = encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }

}
