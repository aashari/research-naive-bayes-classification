<?php

//include all 3rd party vendor
require '../vendor/autoload.php';

/**
 * Naive Bayes Classifier is a simple probabilistic classifiers based on applying
 * Bayes' theorem with strong (naive) independence assumptions between the features.
 *
 * @author     Andi Muqsith Ashari
 * @version    v1.0
 * @date       2016-06-14
 *
 */
class NaiveBayesClassifier
{

    /**
     * Trained words.
     *
     * @var array
     */
    private $trainedWords;

    /**
     * Add word into trained words
     *
     * @param string $class The class name for words.
     * @param string $words The words that classified as $class.
     */
    public function training($class, $words)
    {

        if (!@$this->trainedWords[$class]) {
            $this->trainedWords[$class] = array();
        }

        $arrayOfWord = explode(' ', $words);
        $this->trainedWords[$class] = array_merge($this->trainedWords[$class], $arrayOfWord);
        $this->trainedWords[$class] = array_unique($this->trainedWords[$class]);
    }

    /**
     * Classify words based on trained words using Naive Bayes formula
     *
     * @param string $inWords The words that wanted to classified.
     * @var array of words probability in class
     */
    public function classify($inWords)
    {

        $arrayOfInWords = explode(' ', $inWords);
        $nWords = $this->totalTrainedWords();

        $classifyResult = array();

        foreach ($this->trainedWords as $class => $words) {
            $probabilityOfClass = sizeof($words) / $nWords;
            foreach ($arrayOfInWords as $inWord) {
                $isExists = (int)in_array($inWord, $words);
                $probabilityOfWordInClass = (1 + $isExists) / sizeof($this->trainedWords);
                $probabilityOfClass *= $probabilityOfWordInClass;
            }
            $classifyResult[$class] = $probabilityOfClass;
        }

        arsort($classifyResult);

        return $classifyResult;

    }

    /**
     * Count all total trained words
     *
     * @var int
     */
    private function totalTrainedWords()
    {
        $n = 0;
        foreach ($this->trainedWords as $class => $words) {
            $n += sizeof($words);
        }
        return $n;
    }

}

// example

// $nbc = new NaiveBayesClassifier();

// $nbc->training('en','Enjoy the fair, splurge on snazzy products, expand your network at the Business Matching venue, and get inspired by fashion shows and workshops.');
// $nbc->training('en','One unique part of this event is the Innovation and Design Zone and Design Society booth, where businesses, designers, and entrepreneurs can help each other improve their skills and products, and take businesses to the next level.');
// $nbc->training('en','Browse the rows of booths at the fair and shop for products from all the brands you love, like handcrafted products from Thaniya');

// $nbc->training('id','Mobil yang parkir sembarangan di depan rumah pribadi Wakil Gubernur DKI Jakarta Sandiaga Uno diderek petugas Dinas Perhubungan. Sandiaga mengatakan, tindakan hukum bagi kendaraan yang melanggar tidak boleh tebang pilih');
// $nbc->training('id','Sebelumnya tidak pernah ditindak, saya bilang pantang mundur di depan rumah pimpinan juga harus ditertibkan. Jangan cuma di masyarakat, rumah pimpinan juga dikerahkan pasukan supaya tertib dan tidak pandang bulu');
// $nbc->training('id','Sebelumnya Sandiaga juga pernah menyinggung ini saat berkomentar tentang mobil Ratna Sarumpaet. Sandiaga Uno menilai apa yang dilakukan Ratna memang pelanggaran. ');

// $c1 = $nbc->classify("Enggak boleh, itu melanggar. Walaupun derah sini banyak yang parkir sembarangan. Depan rumah saya apalagi, banyak banget itu," kata Sandiaga ");
// echo json_encode($c1);


// $c2 = $nbc->classify("Enjoy the fair, splurge on snazzy products, expand your network at the Business Matching venue, and get inspired by fashion shows and workshops. ");
// echo json_encode($c2);