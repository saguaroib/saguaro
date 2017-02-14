<?php

class SaguaroRegistExtras {

    public function init($post) {

        $capcode = $this->parseCapcode($post['email']);
        $post['capcode'] = $capcode['cap'];
        $post['email'] = $capcode['email'];

        $post['id'] = substr(crypt(md5($_SERVER["REMOTE_ADDR"] . 'id' . date("Ymd", $time)), 'id'), +3);

        require_once(CORE_DIR . "/regist/inc/tripcode.php");
        $tripcodeClass = new Tripcode;
        $tripcode = $tripcodeClass->format($post['name']);
        $post['tripcode'] = $tripcode['trip'];
        $post['name'] = $tripcode['name'];

        if (FORTUNE_TRIP) {
            if (stripos($post['name'], "#fortune") !== false) {
                require_once("fortune.php");
                $fortune = new Fortune;
                $post['comment'] .= "<br><br>" . $fortune->giveFortune();
                $post['name'] = S_ANONAME;
            }
        }

        if (DICE_ROLL) {
            if ($post['email']) {
                $post['comment'] = $this->diceRoll($post['email'], $post['comment']);
                $post['email']   = '';
            }
        }
        
        return $post;
    }

    private function diceRoll($email, $com) {
        if (preg_match("/dice[ +](\\d+)[ d+](\\d+)(([ +-]+?)(-?\\d+))?/", $email, $match)) {
            $dicetxt     = "Rolled ";
            $dicenum     = min(25, $match[1]);
            $diceside    = $match[2];
            $diceaddexpr = $match[3];
            $dicesign    = $match[4];
            $diceadd     = intval($match[5]);

            for ($i = 0; $i < $dicenum; $i++) {
                $dicerand = mt_rand(1, $diceside);
                if ($i) {
                    $dicetxt .= ", ";
                }
                $dicetxt .= $dicerand;
                $dicesum += $dicerand;
            }

            if ($diceaddexpr) {
                if (strpos($dicesign, "-") > 0) {
                    $diceadd *= -1;
                }
                $dicetxt .= ($diceadd >= 0 ? " + " : " - ") . abs($diceadd);
                $dicesum += $diceadd;
            }

            $dicetxt .= " = $dicesum<br /><br />";
            $com = "<strong>$dicetxt</strong>" . $com;

            return $com;
        }
    }
    
    private function parseCapcode($email) {
        $capcode = explode("#capcode_", $email);

        if (valid($capcode[1])) {
            return [
                'cap'   => $capcode[1],
                'email' => ''
            ];
        }

        return [
            'cap'   => null,
            'email' => ''
        ];
    }
}