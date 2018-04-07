<?php

/**
 *
 *
 * @author     Andi Muqsith Ashari
 * @version    v1.0
 * @date       2016-06-14
 *
 */

class LocationIdentifier
{

    private $location = [];

    /**
     * this function will get all Indonesian geo administrative data from:
     * https://github.com/aashari/geo-database-csv
     */
    public function geoLoader()
    {

        if (!empty($this->location)) {
            return $this->location;
        }

        $adm1 = file_get_contents("https://raw.githubusercontent.com/aashari/geo-database-csv/master/data/geo_subdivision_idn_adm1.csv");
        $adm1 = explode("\n", $adm1);

        foreach ($adm1 as $adm) {

            $col = explode(",", $adm);

            $id = $col[0];
            $id = substr($id, 1, strlen($id) - 2);
            $id = intval($id);

            $name = $col[5];
            $name = substr($name, 1, strlen($name) - 2);

            if ($name && $id) {
                $this->location[$id]['name'] = $name;
                $this->location[$id]['childrens'] = [];
            }

        }

        $adm2 = file_get_contents("https://raw.githubusercontent.com/aashari/geo-database-csv/master/data/geo_subdivision_idn_adm2.csv");
        $adm2 = explode("\n", $adm2);

        foreach ($adm2 as $adm) {

            $col = explode(",", $adm);

            $id = $col[0];
            $id = substr($id, 1, strlen($id) - 2);
            $id = intval($id);

            $idParent = $col[2];
            $idParent = substr($idParent, 1, strlen($idParent) - 2);
            $idParent = intval($idParent);

            $name = $col[5];
            $name = substr($name, 1, strlen($name) - 2);

            if ($name && $id && isset($this->location[$idParent]) && isset($this->location[$idParent]['childrens'])) {
                $this->location[$idParent]['childrens'][$id] = [
                    'name' => $name,
                    'childrens' => []
                ];
            }

        }

        $adm3 = file_get_contents("https://raw.githubusercontent.com/aashari/geo-database-csv/master/data/geo_subdivision_idn_adm3.csv");
        $adm3 = explode("\n", $adm3);

        foreach ($adm3 as $adm) {

            $col = explode(",", $adm);

            $id = $col[0];
            $id = substr($id, 1, strlen($id) - 2);
            $id = intval($id);

            $idParent = $col[2];
            $idParent = substr($idParent, 1, strlen($idParent) - 2);
            $idParent = intval($idParent);

            $name = $col[5];
            $name = substr($name, 1, strlen($name) - 2);

            foreach ($this->location as $l1id => $l1) {
                foreach ($l1['childrens'] as $l12d => $l2) {
                    if ($l12d == $idParent) {
                        $this->location[$l1id]['childrens'][$l12d]['childrens'][$id] = [
                            'name' => $name
                        ];
                    }
                }
            }

        }

        $this->location = array_values($this->location);
        foreach ($this->location as $k => $v) {
            $this->location[$k]['childrens'] = array_values($v['childrens']);
            foreach ($this->location[$k]['childrens'] as $k1 => $v1) {
                $this->location[$k]['childrens'][$k1]['childrens'] = array_values($this->location[$k]['childrens'][$k1]['childrens']);
            }
        }

        return $this->location;

    }

    public function identify($text, $n = 5)
    {

        require_once 'step03_n_gram.php';

        $locationList = $this->geoLoader();

        $ngg = new NGramGenerator();
        $gramList = $ngg->generate($n, $text);

        $foundLocation = [];
        $foundLocationOnGram = [];

        foreach ($locationList as $l1) {

            foreach ($gramList as $idx => $gl) {

                $tf = '  ' . $gl . '  ';
                $wf = ' ' . $l1['name'] . ' ';

                if (strpos($tf, $wf) !== false) {
                    if (!isset($foundLocation[$l1['name']])) {
                        $foundLocation[$l1['name']] = [
                            'name' => $l1['name'],
                            'count' => 0
                        ];
                    }
                    $foundLocationOnGram[] = [
                        'gram_index' => $idx,
                        'gram_found' => $l1['name']
                    ];
                    $foundLocation[$l1['name']]['count']++;
                }
            }

            foreach ($l1['childrens'] as $l2) {

                foreach ($gramList as $idx => $gl) {

                    $tf = '  ' . $gl . '  ';
                    $wf = ' ' . $l2['name'] . ' ';

                    if (strpos($tf, $wf) !== false) {
                        if (!isset($foundLocation[$l1['name']])) {
                            $foundLocation[$l1['name']] = [
                                'name' => $l1['name'],
                                'count' => 0
                            ];
                        }
                        $foundLocationOnGram[] = [
                            'gram_index' => $idx,
                            'gram_found' => $l1['name'] . ' > ' . $l2['name']
                        ];
                        $foundLocation[$l1['name']]['count']++;
                    }
                }

                foreach ($l2['childrens'] as $l3) {
                    foreach ($gramList as $idx => $gl) {

                        $tf = '  ' . $gl . '  ';
                        $wf = ' ' . $l3['name'] . ' ';

                        if (strpos($tf, $wf) !== false) {
                            if (!isset($foundLocation[$l1['name']])) {
                                $foundLocation[$l1['name']] = [
                                    'name' => $l1['name'],
                                    'count' => 0
                                ];
                            }
                            $foundLocationOnGram[] = [
                                'gram_index' => $idx,
                                'gram_found' => $l1['name'] . ' > ' . $l2['name'] . ' > ' . $l3['name']
                            ];
                            $foundLocation[$l1['name']]['count']++;
                        }
                    }
                }

            }

        }

        $foundLocation = array_values($foundLocation);

        foreach ($foundLocation as $flk1 => $flv1) {
            foreach ($foundLocation as $flk2 => $flv2) {
                if ($flv1['count'] > $flv2['count']) {
                    $temp = $foundLocation[$flk1];
                    $foundLocation[$flk1] = $foundLocation[$flk2];
                    $foundLocation[$flk2] = $temp;
                }
            }
        }

        foreach ($foundLocationOnGram as $flk1 => $flv1) {
            foreach ($foundLocationOnGram as $flk2 => $flv2) {
                if ($flv1['gram_index'] < $flv2['gram_index']) {
                    $temp = $foundLocationOnGram[$flk1];
                    $foundLocationOnGram[$flk1] = $foundLocationOnGram[$flk2];
                    $foundLocationOnGram[$flk2] = $temp;
                }
            }
        }


        return [
            'n_gram_result' => $gramList,
            'found_location' => $foundLocation,
            'found_location_on_gram' => $foundLocationOnGram
        ];

    }

}