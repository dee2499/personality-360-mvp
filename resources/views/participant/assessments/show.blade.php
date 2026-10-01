<x-layouts.app>
    @php
        $questions = $assessment->survey->questions;
        $totalQuestions = $questions->count();
        $subjectName = $assessment->isSelfAssessment() ? 'Yourself' : $assessment->subject->name;
    @endphp

    <div class="max-w-3xl mx-auto space-y-6" x-data="{
        currentStep: 0,
        totalSteps: {{ $totalQuestions }},
        answers: {{ json_encode($answers) }},
        confirmModal: false,
        isReadOnly: {{ $isReadOnly ? 'true' : 'false' }},

        answeredCount() {
            return Object.keys(this.answers).filter(k => this.answers[k] !== null && this.answers[k] !== undefined).length;
        },

        isAnswered(questionId) {
            return this.answers[questionId] !== undefined && this.answers[questionId] !== null;
        },

        selectRating(questionId, val) {
            if (this.isReadOnly) return;
            this.answers[questionId] = val;
            
            // Auto advance to next step smoothly if not on last question
            if (this.currentStep < this.totalSteps - 1) {
                setTimeout(() => {
                    this.currentStep++;
                }, 300);
            }
        },

        nextStep() {
            if (this.currentStep < this.totalSteps - 1) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
            }
        },

        canSubmit() {
            return this.answeredCount() === this.totalSteps;
        },

        openSubmitModal() {
            if (!this.canSubmit()) {
                if (window.alertAction) {
                    window.alertAction({
                        title: 'Incomplete Assessment',
                        message: 'Please answer all ' + this.totalSteps + ' questions before submitting. You have answered ' + this.answeredCount() + ' so far.',
                        confirmText: 'Continue Assessment',
                        type: 'warning'
                    });
                } else {
                    alert('Please answer all ' + this.totalSteps + ' questions before submitting.');
                }
                return;
            }
            this.confirmModal = true;
        }
    }">
        <!-- Top Navigation -->
        <div>
            <a href="{{ route('participant.assessments.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to My Assessments</span>
            </a>
        </div>

        <!-- Clean Distraction-Free Assessment Header -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                        {{ $assessment->isSelfAssessment() ? 'Self Assessment' : 'Peer Assessment' }}
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 mt-2">
                        Assessing: <span class="text-indigo-600">{{ $subjectName }}</span>
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">Survey: {{ $assessment->survey->title }}</p>
                </div>

                <!-- Progress Pill -->
                <div class="flex flex-col sm:items-end">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Progress</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-base font-extrabold text-slate-900">
                            <span x-text="answeredCount()"></span> / {{ $totalQuestions }} Answered
                        </span>
                        <span class="text-xs font-bold text-indigo-600" 
                              x-text="Math.round((answeredCount() / totalSteps) * 100) + '%'"></span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + ((answeredCount() / totalSteps) * 100) + '%'"></div>
            </div>

            <!-- Step Pills Grid for Direct Navigation -->
            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                @foreach($questions as $index => $q)
                    <button type="button" @click="currentStep = {{ $index }}"
                            :class="{
                                'bg-indigo-600 text-white font-bold': currentStep === {{ $index }},
                                'bg-emerald-100 text-emerald-800 font-semibold': currentStep !== {{ $index }} && isAnswered({{ $q->id }}),
                                'bg-slate-100 text-slate-500': currentStep !== {{ $index }} && !isAnswered({{ $q->id }})
                            }"
                            class="w-7 h-7 rounded-lg text-xs flex items-center justify-center transition cursor-pointer">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        </div>

        @if($isReadOnly)
            <!-- Completed Notice -->
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs font-semibold">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                    <span>This assessment was completed on {{ $assessment->completed_at?->format('M d, Y H:i') }}. Read-only review mode.</span>
                </div>
                <span class="font-extrabold text-sm">Score: {{ $assessment->total_score }} / {{ $assessment->max_score }}</span>
            </div>
        @endif

        <!-- Form wrapping the questions -->
        <form id="assessmentForm" method="POST" action="{{ route('participant.assessments.submit', $assessment) }}">
            @csrf

            <!-- Question Cards -->
            <div class="space-y-6">
                @foreach($questions as $index => $q)
                    <div x-show="currentStep === {{ $index }}" x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-white p-6 sm:p-10 rounded-3xl border border-slate-200 shadow-sm space-y-8">
                        
                        <!-- Question Indicator -->
                        <div class="flex items-center justify-between text-xs font-bold text-slate-400">
                            <span class="uppercase tracking-wider text-indigo-600">Question {{ $index + 1 }} of {{ $totalQuestions }}</span>
                            <span class="text-slate-400">Rating Scale: 1 (Lowest) – 10 (Highest)</span>
                        </div>

                        <!-- Question Text (Spec Section 15) -->
                        <div class="py-2">
                            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-snug">
                                {{ $q->question_text }}
                            </h2>
                            <p class="text-xs text-slate-400 mt-2">
                                Please rate {{ $subjectName }} objectively based on consistent behaviors and observations.
                            </p>
                        </div>

                        <!-- Rating Control (1 to 10 Scale) -->
                        <div class="space-y-4">
                            <!-- Hidden input for standard form submission -->
                            <input type="hidden" name="answers[{{ $q->id }}]" :value="answers[{{ $q->id }}] ?? ''">

                            <!-- Interactive 1-10 Button Strip -->
                            <div class="grid grid-cols-5 sm:grid-cols-10 gap-2 sm:gap-2.5">
                                @for($val = 1; $val <= 10; $val++)
                                    <button type="button" 
                                            @click="selectRating({{ $q->id }}, {{ $val }})"
                                            :disabled="isReadOnly"
                                            :class="{
                                                'bg-indigo-600 text-white shadow-lg shadow-indigo-200 scale-105 border-indigo-600 ring-4 ring-indigo-100': answers[{{ $q->id }}] == {{ $val }},
                                                'bg-white text-slate-700 border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50': answers[{{ $q->id }}] != {{ $val }} && !isReadOnly,
                                                'bg-slate-50 text-slate-400 border-slate-100 cursor-not-allowed': isReadOnly && answers[{{ $q->id }}] != {{ $val }}
                                            }"
                                            class="aspect-square flex flex-col items-center justify-center rounded-2xl border-2 text-base sm:text-lg font-black transition-all cursor-pointer select-none">
                                        <span>{{ $val }}</span>
                                    </button>
                                @endfor
                            </div>

                            <!-- Scale Legend -->
                            <div class="flex justify-between text-[11px] font-semibold text-slate-400 px-1 pt-1">
                                <span>1 = Strongly Disagree</span>
                                <span>5 = Neutral</span>
                                <span>10 = Outstanding</span>
                            </div>
                        </div>

                        <!-- Navigation Footer -->
                        <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                            <button type="button" @click="prevStep()" 
                                    :disabled="currentStep === 0"
                                    :class="currentStep === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-slate-100 text-slate-700'"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold border border-slate-200 transition">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                <span>Previous</span>
                            </button>

                            <div class="flex items-center gap-2">
                                <template x-if="currentStep < totalSteps - 1">
                                    <button type="button" @click="nextStep()" 
                                            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition cursor-pointer">
                                        <span>Next Question</span>
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </button>
                                </template>

                                <template x-if="currentStep === totalSteps - 1 && !isReadOnly">
                                    <button type="button" @click="openSubmitModal()"
                                            :disabled="!canSubmit()"
                                            :class="canSubmit() ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-100' : 'bg-slate-300 cursor-not-allowed'"
                                            class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-xl text-xs font-bold text-white shadow-sm transition cursor-pointer">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                        <span>Submit Assessment</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Confirmation Modal (Spec Section 15) -->
            <div x-show="confirmModal" 
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-5"
                     @click.away="confirmModal = false">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                        <i data-lucide="check-check" class="w-6 h-6"></i>
                    </div>

                    <div class="text-center">
                        <h3 class="text-lg font-bold text-slate-900">Submit Assessment?</h3>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            You have answered <strong class="text-slate-800" x-text="totalSteps"></strong> of <strong class="text-slate-800" x-text="totalSteps"></strong> questions for <strong class="text-indigo-600">{{ $subjectName }}</strong>.
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Once submitted, your ratings become final and will be factored into their 360-degree synthesis score.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="button" @click="confirmModal = false"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-100 transition">
                            Submit Assessment
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
