<?php
/*

    Read-only API for Saguaro post log... table thing.

    case 'api':
        require_once(CORE_DIR . "/api/api.php");
        $API = new SaguaroAPI;
        echo $API->format($no);
        break;

*/

class SaguaroAPI {
    private $temp = [];

    function format($no) {
        global $my_log;

        $my_log->update_cache();
        $temp = $my_log->cache[$no];
        $out = [
            "links" => [
                "self" => SITE_ROOT_BD . "/" . RES_DIR . "$no.json",
                "parent" => ($temp['resto'] > 0) ? SITE_ROOT_BD . "/" . RES_DIR . $temp['resto'] . ".json" : null
            ],
            "post" => $this->formatPost($no)
        ];

        if ($temp['resto'] == 0) {
            $out['children'] = $this->formatReplies($no);
        }

        return json_encode($out, JSON_UNESCAPED_SLASHES);
    }

    private function formatPost($no) {
        global $my_log;

        $temp = $my_log->cache[$no];
        $bad = ['host', 'pwd', 'children'];
        $rename = ['sub' => 'subject', 'com' => 'comment'];

        //Remove "bad" keys.
        foreach ($bad as $unset) {
            unset($temp[$unset]);
        }

        //Return null if no file, otherwise format the file information then delete the old keys.
        $temp['file'] = (!$temp['fname']) ? null :
        [
            'name' => $temp['fname'],
            'size' => $temp['fsize'],
            'extension' => $temp['ext'],
            'md5' => $temp['md5'],
            'dimensions' => [$temp['w'],$temp['h']],
            'thumb_dimensions' => [$temp['tn_w'],$temp['tn_h']],
        ];
        unset($temp['fname'],$temp['fsize'],$temp['ext'],$temp['md5'],$temp['w'],$temp['h'],$temp['tn_w'],$temp['tn_h']);

        //Format special properties.
        $temp['special'] = [
            'locked' => (bool) $temp['locked'],
            'permasage' => (bool) $temp['permasage'],
            'sticky' => (bool) $temp['sticky']
        ];
        unset($temp['locked'],$temp['permasage'],$temp['sticky']);

        //Rename some keys.
        foreach ($rename as $left=>$right) {
            $temp[$right] = $temp[$left];
            unset($temp[$left]);
        }

        return $temp;
    }

    private function formatReplies($op) {
        global $my_log;

        $log = $my_log->cache;
        $temp = [];

        foreach ($log as $entry) {
            if ($entry['resto'] == $op) {
                array_push($temp, $this->formatPost($entry['no']));
            }
        }

        return $temp;
    }
}
?>