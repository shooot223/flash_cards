<?php
$dirs = [
    __DIR__.'/app/Http/Controllers',
    __DIR__.'/app/Events',
    __DIR__.'/tests/Feature',
    __DIR__.'/resources/views'
];

$replacements = [
    'QuestionTitleCategory' => 'QuizCategory',
    'QuestionTitle' => 'Quiz',
    'questionTitle' => 'quiz',
    'question_titles' => 'quizzes',
    'question_title_categories' => 'quiz_categories',
    'title_id' => 'quiz_id',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && (str_ends_with($file->getFilename(), '.php') || str_ends_with($file->getFilename(), '.blade.php'))) {
            $content = file_get_contents($file->getPathname());
            $newContent = $content;
            foreach ($replacements as $search => $replace) {
                $newContent = str_replace($search, $replace, $newContent);
            }
            if ($content !== $newContent) {
                file_put_contents($file->getPathname(), $newContent);
                echo "Updated: {$file->getPathname()}\n";
            }
        }
    }
}
