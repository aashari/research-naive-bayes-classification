<?php

//include all 3rd party vendor
require '../vendor/autoload.php';

/**
 * Text processing step => tokenizing, stemming and stopping
 * Stemming using Nazief dan Adriani Algorithm
 * PHP source code from Sastrawi
 * https://github.com/sastrawi/sastrawi
 *
 * @author     Andi Muqsith Ashari
 * @version    v1.0
 * @date       2016-06-14
 *
 */
class TextProcessing
{

    public function process($text)
    {
        //in case of text contains html we must remove all those html
        $text = strip_tags($text);

        //create tokenizer object from sastrawi
        //source code: https://github.com/sastrawi/tokenizer
        $tokenizerFactory = new \Sastrawi\Tokenizer\TokenizerFactory();
        $tokenizer = $tokenizerFactory->createDefaultTokenizer();

        //do tokenizing
        $words = $tokenizer->tokenize($text);

        //create stemmer object from sastrawi
        //source code: https://github.com/sastrawi/sastrawi
        $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
        $stemmer = $stemmerFactory->createStemmer();

        //do stemming
        foreach ($words as $idx => $word) {
            $words[$idx] = $stemmer->stem($word);
        }
        //join words array into string
        $words = implode(" ", $words);

        //create stopwordRemover object from sastrawi
        //source code: https://github.com/sastrawi/sastrawi
        $stopWordRemoverFactory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
        $stopWordRemover = $stopWordRemoverFactory->createStopWordRemover();

        //do stopword remover
        $words = $stopWordRemover->remove($words);

        //remove double space
        $words = preg_replace('/\s+/', ' ', $words);

        return $words;

    }

}

// example
// $tp = new TextProcessing();
// echo $tp->process("Aku sangat senang dapat bersamamu selama ini, aku menginginkan kita seperti ini selama lamanya");
//