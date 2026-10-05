<x-layouts.app>
    @php
        $individualCount = $individualQuestions->count();
        $syncCount = $groupSyncQuestions->count();
        $totalSubjects = $subjects->count();
        $totalQuestions = $individualCount + $syncCount;
    @endphp

    <div class="max-w-5xl mx-auto space-y-6" x-data="{
        currentIndex: 0,
        individualCount: {{ $individualCount }},
        syncCount: {{ $syncCount }},
        totalQuestions: {{ $totalQuestions }},
        totalSubjects: {{ $totalSubjects }},
        individualQuestions: {{ Js::from($individualQuestions) }},
        syncQuestions: {{ Js::from($groupSyncQuestions) }},
        subjects: {{ Js::from($subjects) }},
        scores: {{ Js::from($existingScores) }},
        syncScores: {{ Js::from($existingGroupSyncScores) }},
        isReadonly: {{ $isAllCompleted ? 'true' : 'false' }},
        currentUserId: {{ $user->id }},

        init() {
            if (!this.scores || Array.isArray(this.scores)) {
                this.scores = {};
            }
            this.subjects.forEach(s => {
                if (!this.scores[s.id]) {
                    this.scores[s.id] = {};
                }
            });
            if (!this.syncScores || Array.isArray(this.syncScores)) {
                this.syncScores = {};
            }
        },

        isSyncStep() {
            return this.currentIndex >= this.individualCount;
        },

        getCurrentIndividualQuestion() {
            return this.individualQuestions[this.currentIndex] || null;
        },

        getCurrentSyncQuestion() {
            let syncIdx = this.currentIndex - this.individualCount;
            return this.syncQuestions[syncIdx] || null;
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

        getSyncScore(questionId) {
            return this.syncScores[questionId] !== undefined ? this.syncScores[questionId] : null;
        },

        setSyncScore(questionId, val) {
            if (this.isReadonly) return;
            this.syncScores[questionId] = val;
        },

        isQuestionComplete(qIdx) {
            if (qIdx < this.individualCount) {
                let qId = this.individualQuestions[qIdx]?.id;
                if (!qId) return false;
                return this.subjects.every(s => this.scores[s.id] && this.scores[s.id][qId]);
            } else {
                let syncIdx = qIdx - this.individualCount;
                let qId = this.syncQuestions[syncIdx]?.id;
                if (!qId) return false;
                return this.syncScores[qId] !== undefined && this.syncScores[qId] !== null && this.syncScores[qId] !== '';
            }
        },

        currentRatedCount() {
            if (!this.isSyncStep()) {
                let q = this.getCurrentIndividualQuestion();
                if (!q) return 0;
                let count = 0;
                this.subjects.forEach(s => {
                    if (this.scores[s.id] && this.scores[s.id][q.id]) {
                        count++;
                    }
                });
                return count;
            } else {
                let q = this.getCurrentSyncQuestion();
                if (!q) return 0;
                return (this.syncScores[q.id] ? 1 : 0);
            }
        },

        totalCompletedQuestions() {
            let completed = 0;
            for (let i = 0; i < this.totalQuestions; i++) {
                if (this.isQuestionComplete(i)) completed++;
            }
            return completed;
        },

        totalProgressPercent() {
            let totalItems = (this.individualCount * this.totalSubjects) + this.syncCount;
            if (totalItems === 0) return 0;

            let filled = 0;
            this.subjects.forEach(s => {
                this.individualQuestions.forEach(q => {
                    if (this.scores[s.id] && this.scores[s.id][q.id]) filled++;
                });
            });

            this.syncQuestions.forEach(q => {
                if (this.syncScores[q.id] !== undefined && this.syncScores[q.id] !== null && this.syncScores[q.id] !== '') {
                    filled++;
                }
            });

            return Math.min(100, Math.round((filled / totalItems) * 100));
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
                        <span>Exit to Dashboard</span>
                    </a>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $survey->title }}</h1>
                        @if($isAllCompleted)
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                Completed & Submitted
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-600"></i>
                                Change Quotient (CQ) Assessment
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Part 1: 11 Individual Change Journey questions (Self & Peers) • Part 2: 3 Group Alignment (Sync) questions.
                    </p>
                </div>

                <!-- Progress Pill -->
                <div class="flex flex-col sm:items-end gap-1.5 bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-2xl">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Overall Completion</span>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black text-slate-900" x-text="totalProgressPercent() + '%'">0%</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-md" 
                              :class="totalProgressPercent() === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'">
                            <span x-text="totalCompletedQuestions() + ' / ' + totalQuestions + ' Steps Complete'"></span>
                        </span>
                    </div>
                    <div class="w-48 bg-slate-200 rounded-full h-2 overflow-hidden mt-1">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                             :style="'width: ' + totalProgressPercent() + '%'"></div>
                    </div>
                </div>
            </div>

            <!-- Two-Section Navigator (Individual CQ Q1-Q11 + Group Sync Q12-Q14) -->
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-500 uppercase tracking-wider">
                        Assessment Sections & Step Navigator
                    </span>
                    <span class="font-bold text-indigo-600" 
                          x-text="'Step ' + (currentIndex + 1) + ' of ' + totalQuestions"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Part 1 Navigator: Individual CQ (Q1-Q11) -->
                    <div class="bg-slate-50/80 p-3 rounded-2xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                Part 1: Individual CQ (Q1–Q11)
                            </span>
                            <span class="text-slate-400 font-normal">Self + Peers</span>
                        </div>
                        <div class="grid grid-cols-11 gap-1">
                            <template x-for="(q, idx) in individualQuestions" :key="'ind-nav-' + q.id">
                                <button type="button" 
                                        @click="jumpTo(idx)"
                                        class="py-2 rounded-lg text-[10px] font-bold transition flex flex-col items-center justify-center border cursor-pointer select-none"
                                        :class="{
                                            'bg-indigo-600 border-indigo-600 text-white shadow-xs ring-2 ring-indigo-400/30': currentIndex === idx,
                                            'bg-emerald-50 border-emerald-300 text-emerald-700': currentIndex !== idx && isQuestionComplete(idx),
                                            'bg-white border-slate-200 text-slate-600 hover:bg-slate-100': currentIndex !== idx && !isQuestionComplete(idx)
                                        }">
                                    <span x-text="'Q' + (idx + 1)"></span>
                                    <span class="text-[8px] font-mono leading-none" 
                                          x-text="isQuestionComplete(idx) ? '✓' : (currentIndex === idx ? '●' : '○')"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Part 2 Navigator: Group Sync (Q12-Q14) -->
                    <div class="bg-indigo-50/40 p-3 rounded-2xl border border-indigo-100 space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-bold text-indigo-900">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Part 2: Group Sync (Q12–Q14)
                            </span>
                            <span class="text-indigo-600/80 font-normal">My Views on Team</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="(q, sIdx) in syncQuestions" :key="'sync-nav-' + q.id">
                                <button type="button" 
                                        @click="jumpTo(individualCount + sIdx)"
                                        class="py-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 border cursor-pointer select-none"
                                        :class="{
                                            'bg-indigo-700 border-indigo-700 text-white shadow-xs ring-2 ring-indigo-400/30': currentIndex === (individualCount + sIdx),
                                            'bg-emerald-50 border-emerald-300 text-emerald-700': currentIndex !== (individualCount + sIdx) && isQuestionComplete(individualCount + sIdx),
                                            'bg-white border-slate-200 text-slate-700 hover:bg-slate-100': currentIndex !== (individualCount + sIdx) && !isQuestionComplete(individualCount + sIdx)
                                        }">
                                    <span x-text="'Sync ' + (sIdx + 1)"></span>
                                    <span class="text-[10px] font-mono" 
                                          x-text="isQuestionComplete(individualCount + sIdx) ? '✓' : '○'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Container for Final Submission -->
        <form id="matrix-form" method="POST" action="{{ route('participant.surveys.submit-matrix', $survey) }}"
              data-confirm="true"
              data-confirm-title="Submit Complete Change Quotient Assessment?"
              data-confirm-message="You are about to submit evaluations for the 11 individual CQ questions and the 3 Group Sync questions. Once submitted, your ratings become final and confidential."
              data-confirm-btn="Yes, Submit Complete Assessment"
              data-confirm-type="success">
            @csrf

            <!-- Hidden Matrix Inputs dynamically reflected from Alpine scores -->
            <!-- 1. Individual CQ Answers -->
            <template x-for="subject in subjects" :key="'form-subj-' + subject.id">
                <div>
                    <template x-for="q in individualQuestions" :key="'form-q-' + q.id">
                        <input type="hidden" 
                               :name="'answers[' + subject.id + '][' + q.id + ']'" 
                               :value="getScore(subject.id, q.id)">
                    </template>
                </div>
            </template>

            <!-- 2. Group Sync Answers -->
            <template x-for="q in syncQuestions" :key="'form-sync-' + q.id">
                <input type="hidden" 
                       :name="'group_sync[' + q.id + ']'" 
                       :value="getSyncScore(q.id)">
            </template>

            <!-- ========================================== -->
            <!-- PART 1: INDIVIDUAL QUESTIONS SLIDER WINDOW -->
            <!-- ========================================== -->
            <template x-for="(q, qIdx) in individualQuestions" :key="'ind-card-' + q.id">
                <div x-show="currentIndex === qIdx" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="space-y-6">
                    
                    <!-- Question Banner Card -->
                    <div class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-lg space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black uppercase tracking-widest text-indigo-300 bg-white/10 px-3 py-1 rounded-full border border-white/10">
                                    Question <span x-text="qIdx + 1"></span> of <span x-text="individualCount"></span> • Individual CQ
                                </span>
                                <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-500/20"
                                      x-text="q.dimension"></span>
                            </div>
                            <div class="text-xs font-bold text-indigo-200">
                                <span x-text="currentRatedCount()"></span> of <span x-text="totalSubjects"></span> Rated
                            </div>
                        </div>

                        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white leading-snug" x-text="q.question_text"></h2>
                        
                        <!-- Meaningful 1-Score and 10-Score Descriptions (ANCHORS) -->
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 sm:p-4 border border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-rose-500 text-white font-black flex items-center justify-center shrink-0 text-xs shadow-xs">1</span>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-rose-300 tracking-wider">Score 1 Anchor:</span>
                                    <span class="font-bold text-white text-sm" x-text="q.min_score_description"></span>
                                </div>
                            </div>

                            <div class="hidden sm:block text-slate-400 font-bold text-xs uppercase tracking-wider">
                                ⟵ Scale 1 to 10 ⟶
                            </div>

                            <div class="flex items-center gap-2 sm:text-right">
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-emerald-300 tracking-wider">Score 10 Anchor:</span>
                                    <span class="font-bold text-white text-sm" x-text="q.max_score_description"></span>
                                </div>
                                <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white font-black flex items-center justify-center shrink-0 text-xs shadow-xs">10</span>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Rating Table / Rows -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">
                                Rate Team Members for Question <span x-text="qIdx + 1"></span>
                            </h3>
                            <span class="text-xs text-slate-400">Select rating 1 to 10 for each person</span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            <template x-for="(person, pIdx) in subjects" :key="person.id">
                                <div class="py-4 sm:py-5 first:pt-2 transition-all rounded-2xl px-3"
                                     :class="{
                                         'bg-indigo-50/60 border border-indigo-200/80 my-2': person.id === currentUserId,
                                         'hover:bg-slate-50/50': person.id !== currentUserId
                                     }">
                                    
                                    <!-- Person Header -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-3">
                                            <!-- Avatar -->
                                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm shrink-0"
                                                 :class="person.id === currentUserId ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-slate-100 text-slate-700'">
                                                <span x-text="person.name.substring(0, 1)"></span>
                                            </div>

                                            <!-- Name & Badges -->
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-bold text-slate-900 text-sm sm:text-base" 
                                                        x-text="person.id === currentUserId ? 'You (' + person.name + ')' : person.name"></h4>
                                                    
                                                    <template x-if="person.id === currentUserId">
                                                        <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-indigo-600 text-white shadow-xs">
                                                            Self Evaluation
                                                        </span>
                                                    </template>
                                                    <template x-if="person.id !== currentUserId">
                                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                                            Colleague Review
                                                        </span>
                                                    </template>
                                                </div>
                                                <span class="text-[11px] text-slate-400" x-text="person.email"></span>
                                            </div>
                                        </div>

                                        <!-- Rating Selected Badge -->
                                        <div>
                                            <template x-if="getScore(person.id, q.id)">
                                                <span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800">
                                                    <span>Rating:</span>
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
                                                        'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-200 scale-105 z-10 ring-2 ring-indigo-400/40': getScore(person.id, q.id) === scoreVal,
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

            <!-- ========================================== -->
            <!-- PART 2: GROUP SYNC QUESTIONS SLIDER WINDOW -->
            <!-- ========================================== -->
            <template x-for="(q, sIdx) in syncQuestions" :key="'sync-card-' + q.id">
                <div x-show="currentIndex === (individualCount + sIdx)" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="space-y-6">
                    
                    <!-- Group Sync Banner Card -->
                    <div class="bg-gradient-to-br from-emerald-950 via-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-lg space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black uppercase tracking-widest text-emerald-300 bg-white/10 px-3 py-1 rounded-full border border-white/10">
                                    Group Sync Question <span x-text="sIdx + 1"></span> of <span x-text="syncCount"></span>
                                </span>
                                <span class="text-xs font-bold text-indigo-300 bg-indigo-500/20 px-2.5 py-0.5 rounded-full border border-indigo-400/30"
                                      x-text="q.dimension"></span>
                            </div>
                            <div class="text-xs font-bold text-emerald-200">
                                <span x-text="getSyncScore(q.id) ? 'Rated' : 'Pending Rating'"></span>
                            </div>
                        </div>

                        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white leading-snug" x-text="q.question_text"></h2>
                        
                        <p class="text-xs text-slate-300 leading-relaxed">
                            How well does this entire team/cohort operate together on this dimension? Your evaluation is combined anonymously into the cohort's Group Sync index.
                        </p>

                        <!-- Meaningful 1-Score and 10-Score Descriptions (ANCHORS) -->
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 sm:p-4 border border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-rose-500 text-white font-black flex items-center justify-center shrink-0 text-xs shadow-xs">1</span>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-rose-300 tracking-wider">Score 1 Anchor:</span>
                                    <span class="font-bold text-white text-sm" x-text="q.min_score_description"></span>
                                </div>
                            </div>

                            <div class="hidden sm:block text-slate-400 font-bold text-xs uppercase tracking-wider">
                                ⟵ Scale 1 to 10 ⟶
                            </div>

                            <div class="flex items-center gap-2 sm:text-right">
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-emerald-300 tracking-wider">Score 10 Anchor:</span>
                                    <span class="font-bold text-white text-sm" x-text="q.max_score_description"></span>
                                </div>
                                <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white font-black flex items-center justify-center shrink-0 text-xs shadow-xs">10</span>
                            </div>
                        </div>
                    </div>

                    <!-- Group Rating Selector Box -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">
                                    Your Rating for the Entire Group (<span x-text="q.dimension"></span>)
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Rate how you experience the group as a whole on this dimension.</p>
                            </div>

                            <div>
                                <template x-if="getSyncScore(q.id)">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-black px-3.5 py-1.5 rounded-xl bg-emerald-100 text-emerald-800">
                                        <span>Your Group Rating:</span>
                                        <span class="text-base font-black" x-text="getSyncScore(q.id)"></span>
                                        <span class="text-xs font-normal text-emerald-600">/ 10</span>
                                    </span>
                                </template>
                                <template x-if="!getSyncScore(q.id)">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-slate-100 text-slate-400">
                                        <span>Not selected yet</span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- 1 to 10 Rating Selector -->
                        <div class="grid grid-cols-10 gap-1.5 sm:gap-3">
                            <template x-for="scoreVal in [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]" :key="'sync-btn-' + scoreVal">
                                <button type="button" 
                                        @click="setSyncScore(q.id, scoreVal)"
                                        :disabled="isReadonly"
                                        class="h-14 sm:h-16 rounded-2xl text-sm sm:text-base font-black transition-all flex flex-col items-center justify-center border cursor-pointer select-none"
                                        :class="{
                                            'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-200 scale-105 z-10 ring-4 ring-emerald-400/30': getSyncScore(q.id) === scoreVal,
                                            'bg-white border-slate-200 text-slate-700 hover:border-emerald-500 hover:bg-emerald-50/40 hover:scale-102': getSyncScore(q.id) !== scoreVal
                                        }">
                                    <span x-text="scoreVal"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                </div>
            </template>

            <!-- Bottom Sticky Action Controls Bar -->
            <div class="sticky bottom-6 z-30 bg-white/95 backdrop-blur-md rounded-3xl border border-slate-200 p-4 sm:p-5 shadow-xl flex items-center justify-between gap-4 mt-8">
                <!-- Previous Button -->
                <button type="button" 
                        @click="prev()"
                        :disabled="currentIndex === 0"
                        class="inline-flex items-center gap-1.5 px-4 sm:px-6 py-2.5 rounded-2xl text-xs font-bold transition"
                        :class="currentIndex === 0 ? 'text-slate-300 bg-slate-100 cursor-not-allowed' : 'text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 cursor-pointer'">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    <span>Previous Step</span>
                </button>

                <!-- Status Counter Indicator -->
                <div class="text-center hidden sm:block text-xs">
                    <span class="font-extrabold text-slate-800" 
                          x-text="!isSyncStep() ? 'Individual CQ Q' + (currentIndex + 1) + ' of ' + individualCount : 'Group Sync ' + (currentIndex - individualCount + 1) + ' of ' + syncCount"></span>
                    <span class="text-slate-400 mx-1.5">•</span>
                    <span class="font-bold" :class="isQuestionComplete(currentIndex) ? 'text-emerald-600' : 'text-amber-600'"
                          x-text="isQuestionComplete(currentIndex) ? 'Step Complete ✓' : 'In Progress...'"></span>
                </div>

                <!-- Next / Submit Button -->
                <div>
                    <!-- Next Question -->
                    <template x-if="currentIndex < totalQuestions - 1">
                        <button type="button" 
                                @click="next()"
                                class="inline-flex items-center gap-1.5 px-5 sm:px-7 py-2.5 rounded-2xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-100 transition cursor-pointer">
                            <span>Next Step</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </template>

                    <!-- Submit Complete Assessment -->
                    <template x-if="currentIndex === totalQuestions - 1 && !isReadonly">
                        <button type="submit" 
                                :disabled="!canSubmit()"
                                class="inline-flex items-center gap-1.5 px-6 sm:px-8 py-2.5 rounded-2xl text-xs font-black shadow-lg transition"
                                :class="canSubmit() ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-200 cursor-pointer animate-pulse' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Submit Complete Assessment</span>
                        </button>
                    </template>

                    <!-- If Readonly and on last step -->
                    <template x-if="currentIndex === totalQuestions - 1 && isReadonly">
                        <a href="{{ route('participant.assessments.report', $survey) }}"
                           class="inline-flex items-center gap-1.5 px-6 sm:px-8 py-2.5 rounded-2xl text-xs font-black bg-indigo-600 text-white hover:bg-indigo-700 shadow-md transition">
                            <i data-lucide="award" class="w-4 h-4"></i>
                            <span>View My CQ Report</span>
                        </a>
                    </template>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
