<?php

/**
 *
 *
 * @author     Andi Muqsith Ashari
 * @version    v1.0
 * @date       2016-06-14
 *
 */

class NGramGenerator
{

    public function generate($n, $statement)
    {

        $statement = strip_tags($statement);
        $statement = trim($statement);
        $statement = strtolower($statement);

        $tokenList = explode(' ', $statement);

        $nGramList = [];

        for ($k = 0; $k < count($tokenList) - $n + 1; $k++) {

            $s = "";

            $start = $k;
            $end = $k + $n;

            for ($j = $start; $j < $end; $j++) {
                $s = $s . " " . $tokenList[$j];
            }

            $s = trim($s);
            $s = preg_replace('/\s+/', ' ', $s);

            $nGramList[] = $s;

        }

        return $nGramList;

    }

}

// example
//
// $ngg = new NGramGenerator();
// $result = $ngg->generate(5, "Aku sangat senang dapat bersamamu selama ini, aku menginginkan kita seperti ini selama lamanya");

// echo json_encode($result);