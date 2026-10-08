<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Survey;

class ChangeQuotientQuestionService
{
    /**
     * Get the 14 standard Change Quotient (CQ) questions (11 Individual + 3 Group Sync).
     *
     * @return array<int, array{
     *     question_text: string,
     *     peer_question_text: ?string,
     *     dimension: string,
     *     type: string,
     *     min_score_description: string,
     *     max_score_description: string,
     *     sort_order: int
     * }>
     */
    public static function getDefaultQuestions(): array
    {
        return [
            // 11 Individual CQ Journey Questions
            [
                'question_text' => 'Do you see life changing around? (Respond to all names given below)',
                'peer_question_text' => 'Do you see life changing around?',
                'dimension' => 'Awareness of Change',
                'type' => 'individual',
                'min_score_description' => 'No Change',
                'max_score_description' => 'Lot of Change',
                'sort_order' => 1,
            ],
            [
                'question_text' => 'In the recent past, do you know which are top 3 things thats changing for you? (Respond to all names given below)',
                'peer_question_text' => 'In the recent past, do you know which are top 3 things thats changing for this person?',
                'dimension' => 'Understanding Key Changes',
                'type' => 'individual',
                'min_score_description' => 'No Idea at all',
                'max_score_description' => 'I know it clearly',
                'sort_order' => 2,
            ],
            [
                'question_text' => 'The changes that you have observed in recent past have occurred by situation or by your own choice? (Respond to all names given below)',
                'peer_question_text' => 'The changes observed in recent past have occurred by situation or by this person\'s own choice?',
                'dimension' => 'Choice vs Circumstance',
                'type' => 'individual',
                'min_score_description' => 'By Situation',
                'max_score_description' => 'By Choice',
                'sort_order' => 3,
            ],
            [
                'question_text' => 'How much control you think, you have on these changes thats coming your way? (Respond to all names given below)',
                'peer_question_text' => 'How much control does this person have on the changes coming their way?',
                'dimension' => 'Control Over Change',
                'type' => 'individual',
                'min_score_description' => 'No Control',
                'max_score_description' => 'Full Control',
                'sort_order' => 4,
            ],
            [
                'question_text' => 'Do you know on how to manage the new changes that you are experiencing in recent past? (Respond to all names given below)',
                'peer_question_text' => 'Does this person know how to manage the new changes they are experiencing?',
                'dimension' => 'Managing Change Knowledge',
                'type' => 'individual',
                'min_score_description' => 'Dont Know',
                'max_score_description' => 'Yes I know',
                'sort_order' => 5,
            ],
            [
                'question_text' => 'What is your confidence level to independently manage the new changes coming your way? (Respond to all names given below)',
                'peer_question_text' => 'What is this person\'s confidence level to independently manage new changes?',
                'dimension' => 'Confidence in Change',
                'type' => 'individual',
                'min_score_description' => 'Not Confident',
                'max_score_description' => 'Very Confident',
                'sort_order' => 6,
            ],
            [
                'question_text' => 'Did you speak to your friend, mentor, colleague or family member who can help you adopt the new changes? (Respond to all names given below)',
                'peer_question_text' => 'Does this person speak to mentors, colleagues, or others who can help them adopt new changes?',
                'dimension' => 'Seeking Support',
                'type' => 'individual',
                'min_score_description' => 'Not sure whom to reach',
                'max_score_description' => 'I know who to reach & seek help',
                'sort_order' => 7,
            ],
            [
                'question_text' => 'Do you have a clear plan by now on what all actions that you need to take and by when? (Respond to all names given below)',
                'peer_question_text' => 'Does this person have a clear plan on what actions they need to take and by when?',
                'dimension' => 'Action Planning',
                'type' => 'individual',
                'min_score_description' => 'Not yet ready',
                'max_score_description' => 'Yes! have a clear plan in hand',
                'sort_order' => 8,
            ],
            [
                'question_text' => 'Have you started implementing your plan of actions to drive your change? (Respond to all names given below)',
                'peer_question_text' => 'Has this person started implementing their plan of actions to drive change?',
                'dimension' => 'Implementing Plan',
                'type' => 'individual',
                'min_score_description' => 'Still Exploring',
                'max_score_description' => 'started implementing',
                'sort_order' => 9,
            ],
            [
                'question_text' => 'Do you see any improvements or results coming from your implemented actions as per your plan? (Respond to all names given below)',
                'peer_question_text' => 'Do you see improvements or results coming from this person\'s implemented actions?',
                'dimension' => 'Seeing Results',
                'type' => 'individual',
                'min_score_description' => 'Not Yet',
                'max_score_description' => 'Yes I can see some..',
                'sort_order' => 10,
            ],
            [
                'question_text' => 'Have you ever tried supporting a friend or family in any of the above mentioned questions in recent past? (Respond to all names given below)',
                'peer_question_text' => 'Has this person supported colleagues or friends through change in the recent past?',
                'dimension' => 'Supporting Others',
                'type' => 'individual',
                'min_score_description' => 'Concentrating on myself',
                'max_score_description' => 'Yes, extending support',
                'sort_order' => 11,
            ],

            // 3 Group Sync Questions (My Views - About our Group)
            [
                'question_text' => 'Do you believe everyone in this group has a common understanding of the key changes that are happening around us? (Consider as a group and respond)',
                'peer_question_text' => null,
                'dimension' => 'See Together',
                'type' => 'group_sync',
                'min_score_description' => 'No Common Understanding',
                'max_score_description' => 'Complete Common Understanding',
                'sort_order' => 12,
            ],
            [
                'question_text' => 'Do you believe everyone in this group is aligned on what needs to change and the direction we need to take? (Consider as a group and respond)',
                'peer_question_text' => null,
                'dimension' => 'Agree Together',
                'type' => 'group_sync',
                'min_score_description' => 'I dont think so',
                'max_score_description' => 'Yes Completely Aligned',
                'sort_order' => 13,
            ],
            [
                'question_text' => 'Do you believe everyone in this group is aligned and committed to taking the actions needed to make the change happen? (Consider as a group and respond)',
                'peer_question_text' => null,
                'dimension' => 'Act Together',
                'type' => 'group_sync',
                'min_score_description' => 'not aligned not committed',
                'max_score_description' => 'Aligned and Committed',
                'sort_order' => 14,
            ],
        ];
    }

    /**
     * Seed or update the standard 14 CQ questions for a given survey.
     */
    public function seedForSurvey(Survey $survey): void
    {
        $defaultQuestions = static::getDefaultQuestions();

        foreach ($defaultQuestions as $data) {
            Question::updateOrCreate(
                [
                    'survey_id' => $survey->id,
                    'sort_order' => $data['sort_order'],
                ],
                [
                    'question_text' => $data['question_text'],
                    'peer_question_text' => $data['peer_question_text'],
                    'dimension' => $data['dimension'],
                    'type' => $data['type'],
                    'min_score_description' => $data['min_score_description'],
                    'max_score_description' => $data['max_score_description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
