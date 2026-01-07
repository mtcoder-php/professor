<?php

namespace App\Services;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestAnswer;
use Illuminate\Support\Facades\DB;

class TestImportService
{
    protected $results = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];

    /**
     * Import questions from file
     */
    public function importFromFile($testId, $file)
    {
        try {
            $content = $this->readFile($file);
            $questions = $this->parseQuestions($content);

            $this->results['total'] = count($questions);

            $test = Test::findOrFail($testId);

            foreach ($questions as $index => $questionData) {
                try {
                    $this->createQuestion($test, $questionData, $index + 1);
                    $this->results['success']++;
                } catch (\Exception $e) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'question_number' => $index + 1,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Update test counts
            $test->questions_count = $test->questions()->count();
            $test->total_points = $test->questions_count * $test->points_per_question;
            $test->save();

            return $this->results;

        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Read file content
     */
    private function readFile($file)
    {
        $extension = $file->getClientOriginalExtension();

        if ($extension === 'txt') {
            return file_get_contents($file->getRealPath());
        } elseif ($extension === 'docx') {
            // Use PhpWord to read .docx
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }

            return $text;
        }

        throw new \Exception('Fayl formati qo\'llab-quvvatlanmaydi. Faqat .txt va .docx');
    }

    /**
     * Parse questions from content
     */
    private function parseQuestions($content)
    {
        $questions = [];
        $lines = explode("\n", $content);
        $currentQuestion = null;
        $currentAnswers = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) {
                if ($currentQuestion) {
                    $questions[] = [
                        'text' => $currentQuestion,
                        'answers' => $currentAnswers
                    ];
                    $currentQuestion = null;
                    $currentAnswers = [];
                }
                continue;
            }

            // Question line (starts with number and dot)
            if (preg_match('/^(\d+)\.\s*(.+)$/', $line, $matches)) {
                // Save previous question if exists
                if ($currentQuestion) {
                    $questions[] = [
                        'text' => $currentQuestion,
                        'answers' => $currentAnswers
                    ];
                }

                $currentQuestion = $matches[2];
                $currentAnswers = [];
            }
            // Answer line (starts with - or #)
            elseif (preg_match('/^([#-])\s*(.+)$/', $line, $matches)) {
                $isCorrect = ($matches[1] === '#');
                $currentAnswers[] = [
                    'text' => $matches[2],
                    'is_correct' => $isCorrect
                ];
            }
        }

        // Save last question
        if ($currentQuestion) {
            $questions[] = [
                'text' => $currentQuestion,
                'answers' => $currentAnswers
            ];
        }

        return $questions;
    }

    /**
     * Create question with answers
     */
    private function createQuestion($test, $questionData, $order)
    {
        // Validate
        if (empty($questionData['text'])) {
            throw new \Exception('Savol matni bo\'sh');
        }

        if (count($questionData['answers']) < 2) {
            throw new \Exception('Kamida 2 ta javob bo\'lishi kerak');
        }

        $correctAnswers = array_filter($questionData['answers'], fn($a) => $a['is_correct']);
        if (count($correctAnswers) === 0) {
            throw new \Exception('To\'g\'ri javob belgilanmagan (#)');
        }

        DB::beginTransaction();

        try {
            // Create question
            $question = TestQuestion::create([
                'test_id' => $test->id,
                'question_text' => $questionData['text'],
                'question_type' => count($correctAnswers) > 1 ? 'multiple' : 'single',
                'order' => $order,
                'points' => $test->points_per_question
            ]);

            // Create answers
            foreach ($questionData['answers'] as $index => $answer) {
                TestAnswer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answer['text'],
                    'is_correct' => $answer['is_correct'],
                    'order' => $index + 1
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get results
     */
    public function getResults()
    {
        return $this->results;
    }
}
