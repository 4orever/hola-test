<?php

class TestDataProvider {

    const URI = 'https://hola.org/challenges/word_classifier/testcase';
    const TIMES = 800;
    const OUTPUT_DIR = 'data/test/';
    const STATFILE_PATH = 'data/test_stat.log';

    public static function doAction() {
        for($i=1;$i<=self::TIMES;$i++) {
            $data = file_get_contents(self::URI);
            file_put_contents(self::OUTPUT_DIR.time().'.json', $data);
            echo $i." / ".self::TIMES."\n";
        }
    }

    public static function analiseAction() {
        $totalStat = [
            'true' => 0,
            'false' => 0,
            'byLength' => []
        ];

        if ($handle = opendir(self::OUTPUT_DIR)) {
            while (false !== ($entry = readdir($handle))) {
                if($entry === '.' || $entry === '..') continue;
                $data = json_decode(file_get_contents(self::OUTPUT_DIR.$entry), true);

                $stat = self::analise($data);
                print_r($stat);
                $totalStat = self::mergeStatArrays($totalStat, $stat);

            }

            closedir($handle);
        }
        
        print_r($totalStat);
        file_put_contents(self::STATFILE_PATH, print_r($totalStat, true));
    }

    /**
     * @param $data
     * @return array
     */
    private function analise($data) {
        $stat = [
            'true' => 0,
            'false' => 0,
            'byLength' => []
        ];
        foreach ($data as $word => $value) {
            $length = strlen($word);
            if(boolval($value)) $stat['true']++; else $stat['false']++;
            if(!array_key_exists($length, $stat['byLength'])) $stat['byLength'][$length] = 0;
            $stat['byLength'][$length]++;
        }

        return $stat;
    }

    /**
     * @param $arr1
     * @param $arr2
     * @return array
     */
    private function mergeStatArrays($arr1, $arr2) {
        $result = [
            'true' => 0,
            'false' => 0
        ];
        $result['true'] = $arr1['true'] + $arr2['true'];
        $result['false'] = $arr1['false'] + $arr2['false'];

        $lengths1 = array_keys($arr1['byLength']);
        $lengths2 = array_keys($arr1['byLength']);
        if(!is_array($lengths1)) $lengths1 = [];
        if(!is_array($lengths2)) $lengths2 = [];
        $lengths = array_merge($lengths1, $lengths2);

        foreach ($lengths as $length) {
            $v1 = 0;
            $v2 = 0;
            if(array_key_exists($length, $arr1['byLength'])) $v1 = $arr1['byLength'][$length];
            if(array_key_exists($length, $arr1['byLength'])) $v2 = $arr1['byLength'][$length];
            
            $result['byLength'][$length] = $v1 + $v2;
        }
        
        return $result;
    }
}

TestDataProvider::analiseAction();
