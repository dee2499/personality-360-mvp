<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearSurveysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessment:clear-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all surveys, questions, assessments, and submissions while keeping users intact';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Deleting all assessment answers, assessments, and surveys...');

        Schema::disableForeignKeyConstraints();

        $answersCount = AssessmentAnswer::count();
        AssessmentAnswer::truncate();

        $assessmentsCount = Assessment::count();
        Assessment::truncate();

        DB::table('survey_participants')->truncate();

        $questionsCount = Question::count();
        Question::truncate();

        $surveysCount = Survey::withTrashed()->count();
        Survey::withTrashed()->forceDelete();

        Schema::enableForeignKeyConstraints();

        $this->info('Successfully deleted:');
        $this->line("- {$answersCount} submission answer(s)");
        $this->line("- {$assessmentsCount} assessment(s)");
        $this->line("- {$questionsCount} question(s)");
        $this->line("- {$surveysCount} survey(s)");

        return self::SUCCESS;
    }
}
