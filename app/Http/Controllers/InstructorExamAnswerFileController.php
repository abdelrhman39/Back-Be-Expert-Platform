<?php

namespace App\Http\Controllers;

use App\Models\ExamAnswer;
use App\Services\InstructorService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstructorExamAnswerFileController extends Controller
{
    public function __invoke(ExamAnswer $answer, InstructorService $instructors): StreamedResponse|Response
    {
        $answer->loadMissing('attempt.exam.section');
        $section = $answer->attempt?->exam?->section;

        abort_unless($section && $answer->file_path, 404);
        $instructors->authorizeSection(request()->user(), $section);
        $instructors->authorizePermission(request()->user(), 'instructor.exam_attempts.grade');
        abort_unless(Storage::disk('local')->exists($answer->file_path), 404);

        return Storage::disk('local')->download(
            $answer->file_path,
            $answer->file_original_name ?: basename($answer->file_path),
        );
    }
}
