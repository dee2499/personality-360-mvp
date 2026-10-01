<x-layouts.app>
    @php
        $totalQuestions = $questions->count();
        $totalSubjects = $subjects->count();
    @endphp

    <div class="max-w-5xl mx-auto space-y-6" x-data="{
        currentIndex: 0,
        totalQuestions: {{ $totalQuestions }},
        totalSubjects: {{ $totalSubjects }},
        questions: {{ Js::from($questions) }},
        subjects: {{ Js::from($subjects) }},
        scores: {{ Js::from($existingScores) }},
        isReadonly: {{ $isAllCompleted ? 'true' : 'false' }},

        init() {
            // Ensure data structures are clean
            if (!this.scores || Array.isArray(this.scores)) {
                this.scores = {};
            }
            this.subjects.forEach(s => {
                if (!this.scores[s.id]) {
                    this.scores[s.id] = {};
                }
            });
        },

        getScore(subjectId, questionId) {
            return this.scores[subjectId] ? this.scores[subjectId][questionId] : null;
        },

        setScore(subjectId, questionId, val) {
            if (this.isReadonly) return;
            if (!this.scores[subjectId]) {
                this.scores[subjectId] = {};
            }
            this.scores[subjectId][questionId] = val;
        },

        isSubjectRatedForCurrent(subjectId) {
            let qId = this.questions[this.currentIndex]?.id;
            return this.scores[subjectId] && this.scores[subjectId][qId] !== undefined && this.scores[subjectId][qId] !== null;
        },

        currentRatedCount() {
            let qId = this.questions[this.currentIndex]?.id;
            let count = 0;
            this.subjects.forEach(s => {
                if (this.scores[s.id] && this.scores[s.id][qId]) {
                    count++;
                }
            });
            return count;
        },

        isQuestionComplete(qIdx) {
            let qId = this.questions[qIdx]?.id;
            if (!qId) return false;
            return this.subjects.every(s => this.scores[s.id] && this.scores[s.id][qId]);
        },

        totalCompletedQuestions() {
            let completed = 0;
            for (let i = 0; i < this.totalQuestions; i++) {
                if (this.isQuestionComplete(i)) completed++;
            }
            return completed;
        },

        totalProgressPercent() {
            let totalPossible = this.totalQuestions * this.totalSubjects;
            if (totalPossible === 0) return 0;
            let filled = 0;
            this.subjects.forEach(s => {
                this.questions.forEach(q => {
                    if (this.scores[s.id] && this.scores[s.id][q.id]) filled++;
                });
            });
            return Math.round((filled / totalPossible) * 100);
        },

        next() {
            if (this.currentIndex < this.totalQuestions - 1) {
                this.currentIndex++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        prev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        jumpTo(index) {
            this.currentIndex = index;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        canSubmit() {
            return this.totalProgressPercent() === 100;
        }
    }">

        <!-- Top Header & Progress Overview -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <a href="{{ route('participant.assessments.index') }}" 
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-2">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Exit to My Assessments</span>
                    </a>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $survey->title }}</h1>
                        @if($isAllCompleted)
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                Completed & Submitted
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-600"></i>
                                360 Cohort Evaluation
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Evaluate all <strong class="text-slate-700">{{ $totalSubjects }} team members</strong> across all 11 behavioral competency dimensions.
                    </p>
                </div>

                <!-- Progress Pill -->
                <div class="flex flex-col sm:items-end gap-1.5 bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-2xl">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Overall Survey Progress</span>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black text-slate-900" x-text="totalProgressPercent() + '%'">0%</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-md" 
                              :class="totalProgressPercent() === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'">
                            <span x-text="totalCompletedQuestions() + ' / ' + totalQuestions + ' Questions Done'"></span>
                        </span>
                    </div>
                    <div class="w-48 bg-slate-200 rounded-full h-2 overflow-hidden mt-1">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                             :style="'width: ' + totalProgressPercent() + '%'"></div>
                    </div>
                </div>
            </div>

            <!-- Question Slider Tabs (1 to 11) -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Question Navigator (Click any to jump)
                    </span>
                    <span class="text-xs font-bold text-indigo-600" x-text="'Window ' + (currentIndex + 1) + ' of ' + totalQuestions"></span>
                </div>

                <div class="grid grid-cols-11 gap-1.5 sm:gap-2">
                    <template x-for="(q, idx) in questions" :key="q.id">
                        <button type="button" 
                                @click="jumpTo(idx)"
                                class="py-2.5 rounded-xl text-xs font-bold transition flex flex-col items-center justify-center gap-0.5 border"
                                :class="{
                                    'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-100 ring-2 ring-indigo-400/30': currentIndex === idx,
                                    'bg-emerald-50 border-emerald-300 text-emerald-700': currentIndex !== idx && isQuestionComplete(idx),
                                    'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100': currentIndex !== idx && !isQuestionComplete(idx)
                                }">
                            <span class="text-[11px]" x-text="'Q' + (idx + 1)"></span>
                            <span class="text-[8px] font-mono leading-none" 
                                  x-text="isQuestionComplete(idx) ? '✓' : (currentIndex === idx ? '●' : '○')"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Form Container for Final Submission -->
        <form id="matrix-form" method="POST" action="{{ route('participant.surveys.submit-matrix', $survey) }}"
              data-confirm="true"
              data-confirm-title="Submit All 360 Evaluations?"
              data-confirm-message="You are about to submit evaluations for all {{ $totalSubjects }} team members across all {{ $totalQuestions }} competency questions. Once submitted, your ratings become final and will be factored into their 360-degree synthesis scores."
              data-confirm-btn="Yes, Submit Evaluations"
              data-confirm-type="success">
            @csrf

            <!-- Hidden Matrix Inputs dynamically reflected from Alpine scores -->
            <template x-for="subject in subjects" :key="'form-subj-' + subject.id">
                <div>
                    <template x-for="q in questions" :key="'form-q-' + q.id">
                        <input type="hidden" 
                               :name="'answers[' + subject.id + '][' + q.id + ']'" 
                               :value="getScore(subject.id, q.id)">
                    </template>
                </div>
            </template>

            <!-- Active Question Window (The Slider Window) -->
            <div class="space-y-6">
                <template x-for="(q, qIdx) in questions" :key="q.id">
                    <div x-show="currentIndex === qIdx" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-x-4"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         class="space-y-6">
                        
                        <!-- Question Banner Card -->
                        <div class="bg-gradient-to-br from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-lg space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase tracking-widest text-indigo-300 bg-white/10 px-3 py-1 rounded-full border border-white/10">
                                    Question <span x-text="qIdx + 1"></span> of <span x-text="totalQuestions"></span>
                                </span>
                                <div class="text-xs font-bold text-indigo-200">
                                    <span x-text="currentRatedCount()"></span> of <span x-text="totalSubjects"></span> Colleagues Rated
                                </div>
                            </div>

                            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white leading-snug" x-text="q.question_text"></h2>
                            
                            <div class="flex items-center justify-between text-xs text-indigo-200 pt-2 border-t border-white/10">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="info" class="w-4 h-4 text-indigo-400"></i>
                                    Scale: 1 (Lowest / Needs Improvement) to 10 (Highest / Exceptional Role Model)
                                </span>
                                <span class="hidden sm:inline-block font-mono text-[11px] text-indigo-300">
                                    Section 42 Behavioral Dimension
                                </span>
                            </div>
                        </div>

                        <!-- Subjects Rating Table / Rows -->
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">
                                    Team Members to Rate for Question <span x-text="qIdx + 1"></span>
                                </h3>
                                <span class="text-xs text-slate-400">Select rating 1 to 10 for each person</span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                <template x-for="(person, pIdx) in subjects" :key="person.id">
                                    <div class="py-4 sm:py-5 first:pt-2 transition-all rounded-2xl px-3"
                                         :class="{
                                             'bg-indigo-50/50 border border-indigo-100/80 my-2': person.id === {{ $user->id }},
                                             'hover:bg-slate-50/50': person.id !== {{ $user->id }}
                                         }">
                                        
                                        <!-- Row Header: Person Info -->
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                                            <div class="flex items-center gap-3">
                                                <!-- Avatar -->
                                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm shrink-0"
                                                     :class="person.id === {{ $user->id }} ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-slate-100 text-slate-700'">
                                                    <span x-text="person.name.substring(0, 1)"></span>
                                                </div>

                                                <!-- Name & Badges -->
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <h4 class="font-bold text-slate-900 text-sm sm:text-base" 
                                                            x-text="person.id === {{ $user->id }} ? 'You (' + person.name + ')' : person.name"></h4>
                                                        
                                                        <template x-if="person.id === {{ $user->id }}">
                                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-indigo-600 text-white shadow-xs">
                                                                Self Evaluation
                                                            </span>
                                                        </template>
                                                        <template x-if="person.id !== {{ $user->id }}">
                                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                                                Colleague
                                                            </span>
                                                        </template>
                                                    </div>
                                                    <span class="text-[11px] text-slate-400" x-text="person.email"></span>
                                                </div>
                                            </div>

                                            <!-- Rating Selected Badge -->
                                            <div>
                                                <template x-if="getScore(person.id, q.id)">
                                                    <span class="inline-flex items-center gap-1 text-xs font-extrabold px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800">
                                                        <span>Selected:</span>
                                                        <span class="text-sm font-black" x-text="getScore(person.id, q.id)"></span>
                                                        <span class="text-[10px] font-normal text-emerald-600">/ 10</span>
                                                    </span>
                                                </template>
                                                <template x-if="!getScore(person.id, q.id)">
                                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-slate-100 text-slate-400">
                                                        <span>Not rated yet</span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- 1 to 10 Rating Buttons Box Row -->
                                        <div class="grid grid-cols-10 gap-1 sm:gap-2">
                                            <template x-for="scoreVal in [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]" :key="scoreVal">
                                                <button type="button" 
                                                        @click="setScore(person.id, q.id, scoreVal)"
                                                        :disabled="isReadonly"
                                                        class="h-11 sm:h-12 rounded-xl text-xs sm:text-sm font-black transition-all flex flex-col items-center justify-center border cursor-pointer select-none"
                                                        :class="{
                                                            'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-200 scale-105 z-10': getScore(person.id, q.id) === scoreVal,
                                                            'bg-white border-slate-200 text-slate-700 hover:border-indigo-400 hover:bg-indigo-50/40 hover:scale-102': getScore(person.id, q.id) !== scoreVal
                                                        }">
                                                    <span x-text="scoreVal"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            <!-- Bottom Sticky Action Controls Bar -->
            <div class="sticky bottom-6 z-30 bg-white/95 backdrop-blur-md rounded-3xl border border-slate-200 p-4 sm:p-5 shadow-xl flex items-center justify-between gap-4 mt-8">
                <!-- Previous Button -->
                <button type="button" 
                        @click="prev()"
                        :disabled="currentIndex === 0"
                        class="inline-flex items-center gap-1.5 px-4 sm:px-6 py-2.5 rounded-2xl text-xs font-bold transition"
                        :class="currentIndex === 0 ? 'text-slate-300 bg-slate-100 cursor-not-allowed' : 'text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 cursor-pointer'">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    <span>Previous Question</span>
                </button>

                <!-- Status Counter Indicator -->
                <div class="text-center hidden sm:block text-xs">
                    <span class="font-extrabold text-slate-800" x-text="'Question ' + (currentIndex + 1) + ' of ' + totalQuestions"></span>
                    <span class="text-slate-400 mx-1.5">•</span>
                    <span class="font-bold" :class="isQuestionComplete(currentIndex) ? 'text-emerald-600' : 'text-amber-600'"
                          x-text="currentRatedCount() + ' of ' + totalSubjects + ' rated'"></span>
                </div>

                <!-- Next / Submit Button -->
                <div>
                    <!-- If not on last question: Next Question -->
                    <template x-if="currentIndex < totalQuestions - 1">
                        <button type="button" 
                                @click="next()"
                                class="inline-flex items-center gap-1.5 px-5 sm:px-7 py-2.5 rounded-2xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-100 transition cursor-pointer">
                            <span>Next Question</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </template>

                    <!-- If on last question (Question 11) and not readonly: Submit All Button -->
                    <template x-if="currentIndex === totalQuestions - 1 && !isReadonly">
                        <button type="submit" 
                                :disabled="!canSubmit()"
                                class="inline-flex items-center gap-2 px-6 sm:px-8 py-2.5 rounded-2xl text-xs font-black text-white shadow-lg transition"
                                :class="canSubmit() ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200 cursor-pointer' : 'bg-slate-400 opacity-60 cursor-not-allowed'">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            <span x-text="canSubmit() ? 'Submit All 360 Evaluations' : 'Complete All Ratings to Submit'"></span>
                        </button>
                    </template>

                    <!-- If already completed: Done button -->
                    <template x-if="isReadonly">
                        <a href="{{ route('participant.assessments.index') }}"
                           class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-2xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 transition">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Return to Dashboard</span>
                        </a>
                    </template>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
