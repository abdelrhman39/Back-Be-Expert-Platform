@if ($relatedCourses->isNotEmpty())
    <section class="recent-works py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>دورات ذات صلة</h3>
            </div>
            <div class="gigs-slider owl-carousel owl-rtl">
                @foreach ($relatedCourses as $related)
                    @include('partials.catalog.course-card-home', ['course' => $related])
                @endforeach
            </div>
        </div>
    </section>
@endif
