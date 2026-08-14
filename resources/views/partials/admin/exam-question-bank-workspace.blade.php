<section class="admin-crud-card admin-exam-bank-workspace" x-data="{ tab: 'bank' }">
    <header class="admin-exam-bank-head">
        <div>
            <span class="admin-exam-bank-eyebrow"><i class="fa-solid fa-box-archive"></i> مركز إدارة المحتوى</span>
            <h2>بنك أسئلة المقرر</h2>
            <p>نظّم الأسئلة، كوّن نماذج عشوائية، أو انقل الأسئلة دفعة واحدة.</p>
        </div>
        <div class="admin-exam-bank-actions">
            <button type="button" @click="window.dispatchEvent(new CustomEvent('open-exam-help', { detail: { section: 'bank' } }))" class="admin-btn-secondary admin-btn-secondary--sm" title="شرح مركز إدارة المحتوى">
                <i class="fa-regular fa-circle-question"></i> شرح هذا القسم
            </button>
            <button type="button" wire:click="exportQuestions" class="admin-btn-secondary admin-btn-secondary--sm">
                <i class="fa-solid fa-file-export"></i> تصدير CSV
            </button>
        </div>
    </header>

    <nav class="admin-exam-bank-tabs" aria-label="أدوات بنك الأسئلة">
        <button type="button" @click="tab='bank'" :class="{ 'is-active': tab==='bank' }"><i class="fa-solid fa-magnifying-glass"></i> استعراض البنك</button>
        <button type="button" @click="tab='pool'" :class="{ 'is-active': tab==='pool' }"><i class="fa-solid fa-shuffle"></i> مجموعة عشوائية</button>
        <button type="button" @click="tab='import'" :class="{ 'is-active': tab==='import' }"><i class="fa-solid fa-file-import"></i> استيراد وتصنيفات</button>
    </nav>

    <div x-show="tab==='bank'" x-cloak>
        <div class="admin-exam-bank-filters">
            <label class="admin-exam-bank-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" wire:model.live.debounce.350ms="bankSearch" placeholder="ابحث في نص السؤال أو الوسوم...">
            </label>
            <select wire:model.live="bankCategoryId">
                <option value="">كل التصنيفات</option>
                @foreach($this->questionCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->parent_id ? '↳ ' : '' }}{{ $category->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="bankType">
                <option value="">كل الأنواع</option>
                @foreach(\App\Support\ExamOptions::questionTypes() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
            </select>
            <select wire:model.live="bankDifficulty">
                <option value="">كل المستويات</option>
                @foreach(\App\Support\ExamOptions::difficulties() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
            </select>
        </div>

        <div class="admin-exam-bank-summary">
            <span><strong>{{ $this->bankQuestions->count() }}</strong> سؤال متاح للإضافة</span>
            <small>الأسئلة الموجودة بالفعل في الاختبار مستبعدة تلقائياً.</small>
        </div>

        <div class="admin-exam-bank">
            @forelse($this->bankQuestions as $question)
                <article wire:key="admin-bank-{{ $question->id }}">
                    <div class="admin-exam-bank-card__main">
                        <div class="admin-exam-bank-card__meta">
                            <span>{{ \App\Support\ExamOptions::questionTypeLabel($question->type) }}</span>
                            <span class="difficulty-{{ $question->difficulty }}">{{ \App\Support\ExamOptions::difficulties()[$question->difficulty] ?? $question->difficulty }}</span>
                            @if($question->category)<span><i class="fa-solid fa-folder"></i> {{ $question->category->name }}</span>@endif
                        </div>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($question->prompt), 180) }}</p>
                        @if($question->tags)
                            <div class="admin-exam-bank-card__tags">
                                @foreach(array_slice($question->tags, 0, 4) as $tag)<small>#{{ $tag }}</small>@endforeach
                            </div>
                        @endif
                    </div>
                    <div class="admin-exam-bank-card__side">
                        <strong>{{ $question->default_points }} <small>درجة</small></strong>
                        <button type="button" wire:click="attachQuestion({{ $question->id }})" class="admin-btn-secondary admin-btn-secondary--sm">
                            <i class="fa-solid fa-plus"></i> إضافة
                        </button>
                    </div>
                </article>
            @empty
                <div class="admin-exam-bank-empty">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                    <strong>لا توجد أسئلة مطابقة</strong>
                    <span>غيّر عوامل التصفية أو استورد أسئلة جديدة إلى البنك.</span>
                </div>
            @endforelse
        </div>
    </div>

    <div x-show="tab==='pool'" x-cloak class="admin-exam-pool-panel">
        @php
            $activePart = $this->parts->first();
            $activePool = $activePart?->pool_filters ?? [];
        @endphp
        <div class="admin-exam-pool-intro">
            <div class="admin-exam-pool-icon"><i class="fa-solid fa-dice"></i></div>
            <div>
                <h3>توليد نموذج مختلف لكل طالب</h3>
                <p>يتم تثبيت قائمة الأسئلة المرشحة عند الحفظ، ثم اختيار العدد المطلوب عشوائياً لكل محاولة.</p>
            </div>
            @if(!empty($activePool['question_ids']))
                <span class="admin-exam-pool-active"><i class="fa-solid fa-circle-check"></i> مفعّلة</span>
            @endif
        </div>

        <form wire:submit="saveRandomPool" class="admin-exam-pool-form">
            <label class="admin-field">
                <span>التصنيف</span>
                <select class="admin-control" wire:model.live="randomCategoryId">
                    <option value="">كل التصنيفات</option>
                    @foreach($this->questionCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->parent_id ? '↳ ' : '' }}{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="admin-field">
                <span>نوع السؤال</span>
                <select class="admin-control" wire:model.live="randomType">
                    <option value="">كل الأنواع</option>
                    @foreach(\App\Support\ExamOptions::questionTypes() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </select>
            </label>
            <label class="admin-field">
                <span>الصعوبة</span>
                <select class="admin-control" wire:model.live="randomDifficulty">
                    <option value="">كل المستويات</option>
                    @foreach(\App\Support\ExamOptions::difficulties() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </select>
            </label>
            <label class="admin-field">
                <span>عدد الأسئلة المختارة *</span>
                <input type="number" min="1" max="500" class="admin-control" wire:model="randomCount">
                @error('randomCount')<small class="admin-field-error">{{ $message }}</small>@enderror
            </label>
            <label class="admin-field">
                <span>درجة السؤال الواحد *</span>
                <input type="number" min=".01" step=".01" class="admin-control" wire:model="randomPoints">
            </label>
            <div class="admin-exam-pool-preview">
                <span>الأسئلة المطابقة الآن</span>
                <strong>{{ $this->poolCandidateCount }}</strong>
                <small>سيتم تثبيت هذه القائمة عند الحفظ</small>
            </div>
            <div class="admin-exam-pool-footer">
                @if(!empty($activePool['question_ids']))
                    <button type="button" wire:click="disableRandomPool" wire:confirm="إيقاف المجموعة العشوائية؟ لن تتأثر الأسئلة الثابتة." class="admin-btn-secondary admin-btn-secondary--sm admin-exam-danger-btn">
                        إيقاف المجموعة
                    </button>
                @endif
                <button type="submit" class="admin-btn-primary admin-btn-primary--sm" wire:loading.attr="disabled">
                    <i class="fa-solid fa-floppy-disk"></i> حفظ وتثبيت المجموعة
                </button>
            </div>
        </form>
    </div>

    <div x-show="tab==='import'" x-cloak class="admin-exam-import-layout">
        <form wire:submit="importQuestions" class="admin-exam-import-card">
            <div class="admin-exam-import-card__icon"><i class="fa-solid fa-file-csv"></i></div>
            <h3>استيراد أسئلة CSV</h3>
            <p>حجم أقصى 2MB. تتم مراجعة جميع الصفوف داخل معاملة واحدة؛ عند وجود خطأ لن يُستورد ملف ناقص.</p>
            <label class="admin-exam-file-drop">
                <input type="file" wire:model="importFile" accept=".csv,text/csv">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>{{ $importFile?->getClientOriginalName() ?? 'اختر ملف CSV' }}</span>
            </label>
            @error('importFile')<small class="admin-field-error">{{ $message }}</small>@enderror
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm" wire:loading.attr="disabled">
                <i class="fa-solid fa-file-import"></i> استيراد إلى البنك
            </button>
            <small class="admin-exam-import-hint">يمكنك تصدير البنك أولاً للحصول على نموذج بالأعمدة والصيغة الصحيحة.</small>
        </form>

        <form wire:submit="createCategory" class="admin-exam-category-card">
            <div class="admin-exam-category-card__head">
                <div class="admin-exam-import-card__icon"><i class="fa-solid fa-folder-tree"></i></div>
                <div><h3>تصنيف جديد</h3><p>أنشئ موضوعاً رئيسياً أو تصنيفاً فرعياً.</p></div>
            </div>
            <label class="admin-field">
                <span>اسم التصنيف *</span>
                <input type="text" class="admin-control" wire:model="newCategoryName" placeholder="مثال: الوحدة الأولى">
                @error('newCategoryName')<small class="admin-field-error">{{ $message }}</small>@enderror
            </label>
            <label class="admin-field">
                <span>التصنيف الأب</span>
                <select class="admin-control" wire:model="newCategoryParentId">
                    <option value="">تصنيف رئيسي</option>
                    @foreach($this->questionCategories->whereNull('parent_id') as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-folder-plus"></i> إنشاء التصنيف</button>
            <div class="admin-exam-category-list">
                @forelse($this->questionCategories as $category)
                    <span class="{{ $category->parent_id ? 'is-child' : '' }}">
                        <i class="fa-regular fa-folder"></i> {{ $category->name }}
                        <small>{{ $category->questions_count }}</small>
                    </span>
                @empty
                    <small>لم تُنشأ تصنيفات بعد.</small>
                @endforelse
            </div>
        </form>
    </div>
</section>
