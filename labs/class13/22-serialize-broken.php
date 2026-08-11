<?php

// This class would CRASH if stored in $_SESSION without __serialize/__unserialize.
// Uncomment the magic methods to make it work.

class BrokenQuiz
{
    private array $questions;

    public function __construct()
    {
        $this->questions = ['Q1', 'Q2', 'Q3'];
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    /*
    public function __serialize(): array
    {
        return ['questions' => $this->questions];
    }

    public function __unserialize(array $data): void
    {
        $this->questions = $data['questions'];
    }
    */
}

session_start();

if (!isset($_SESSION['quiz'])) {
    $_SESSION['quiz'] = new BrokenQuiz();
}

$quiz = $_SESSION['quiz'];

// This WILL crash on the SECOND request:
// Fatal error: Typed property BrokenQuiz::$questions must not
// be accessed before initialization
echo '<pre>';
var_dump($quiz->getQuestions());
echo '</pre>';
