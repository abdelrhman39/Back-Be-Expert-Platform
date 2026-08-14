@php($locale = app()->getLocale())
@if ($fields->isNotEmpty())
    <section id="section-fields" class="popular-section">
        <div class="popular-img">
            <div class="popular-img-right">
                <img src="{{ static_asset('assets/shape-08.png') }}" alt="Shape">
            </div>
        </div>
        <div class="container">
            <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                <h2 class=""> المجالات <span> الأكثر طلباً </span> </h2>
            </div>
            <div class="gigs-card-cat owl-carousel owl-rtl">
                @foreach ($fields as $field)
                    <div class="category-grid flex-fill">
                        <a href="{{ $field->coursesIndexUrl($locale) }}">
                            <div class="popular-icon">
                                <span>
                                    <img src="{{ $field->iconUrl() }}" alt="{{ $field->displayTitle() }}">
                                </span>
                            </div>
                            <div class="popular-content">
                                <h5>{{ $field->displayTitle() }}</h5>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="w-100 text-center mt-4">
                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">
                    جميع المجالات
                </a>
            </div>
        </div>
    </section>
@endif
