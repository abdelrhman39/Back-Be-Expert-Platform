@php($locale = app()->getLocale())
<!-- /ramzy -->
            
        
    <script></script>

    <!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="breadcrumb-img">
        <div class="breadcrumb-left">
            <img src="{{ static_asset(platform_campus_path('aerial')) }}" alt="img">
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                                                    <li class="breadcrumb-item ">
                                                                    <a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a>
                                                            </li>
                                                    <li class="breadcrumb-item active" aria-current="page">
                                                                    الدورات
                                                            </li>
                                            </ol>
                </nav>
                <h1 class="breadcrumb-title">
                    الدورات
                </h1>
                

                <!-- Service Slider -->
                <div class="service-sliders owl-carousel owl-rtl owl-loaded owl-drag">
                                    <div class="owl-stage-outer"><div class="owl-stage"></div></div><div class="owl-dots disabled"></div></div>

            </div>
        </div>
    </div>
</div>
<!-- /Breadcrumb -->
    
    <!-- Page Content -->
    <div class="page-content pb-0" style="transform: none;">
        <div class="container" style="transform: none;">
            <form id="filter_form" style="transform: none;">
                <!-- Title -->
                <div class="title-section">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="title-header">
                                <h2> الدورات </h2>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="title-filter">
                                <div class="form-group search-group">
                                    <a href="javascript:void(0);" onclick="document.getElementById(&#39;filter_form&#39;).submit();">
                                        <span class="search-icon"><i class="feather-search"></i></span>
                                    </a>
                                    <input type="text" name="search_input_courses" class="form-control" placeholder="بحث" value="">
                                </div>
                                <div class="search-filter-selected">
                                    <div class="form-group">
                                        <select class="form-control select select2 select2-hidden-accessible" name="sorting" onchange="this.form.submit()" data-select2-id="1" tabindex="-1" aria-hidden="true">
                                            <option value="latest" data-select2-id="3"> الأحدث </option>
                                            <option value="oldest"> الأقدم </option>
                                        </select><span class="select2 select2-container select2-container--default" dir="rtl" data-select2-id="2" style="width: 100%;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-sorting-1z-container"><span class="select2-selection__rendered" id="select2-sorting-1z-container" role="textbox" aria-readonly="true" title=" الأحدث "> الأحدث </span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Title -->

                <!-- Search Details -->
                <div class="service-gigs" style="transform: none;">
                    <div class="row" style="transform: none;">

                        <!-- Sidebar -->
                        <div class="col-lg-4 theiaStickySidebar" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">
                            
                        <div class="theiaStickySidebar" style="padding-top: 0px; padding-bottom: 1px; position: fixed; transform: translateY(30px); width: 416px; top: 50px; left: 1189.5px;"><div class="sidebar-widget">
                                
                                <div class="sidebar-body p-0">

                                    <!-- Categories -->
                                    <div class="collapse-card">
                                        <h4 class="card-title">
                                            <a class="" data-bs-toggle="collapse" href="{{ route('courses.index', ['locale' => $locale]) }}#categories" aria-expanded="true">
                                                <img src="{{ static_asset('assets/category-icon.svg') }}" alt="icon"> الأقسام                                            </a>
                                        </h4>
                                        <div id="categories" class="collapse show">
                                            <div class="collapse-body">
                                                <ul class="checkbox-list">
                                                                                                                                                                        <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="categories[]" value="3" id="category-3">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        منتج مهارات </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="categories[]" value="4" id="category-4">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        برامج مهارات عامة </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="categories[]" value="5" id="category-5">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        برامج مهارات مهنية </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="categories[]" value="6" id="category-6">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        برامج مهارات مهنية-باحثين عن العمل </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="categories[]" value="7" id="category-7">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        الزمالات المهنية </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <li>
                                                            <a href="javascript:void(0);" class="viewall-button-one" data-show_less="home.show less" data-show_all="عرض الكل"><span>
                                                                    عرض الكل                                                                </span></a>
                                                        </li>
                                                        <li>
                                                            <div class="view-content">
                                                                <div class="viewall-one" style="display: none;">
                                                                    <ul>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <li>
                                                                                    <label class="custom_check">
                                                                                        <input type="checkbox" name="categories[]" value="10" id="category-10">
                                                                                        <span class="checkmark"></span>
                                                                                        <span class="checked-title">
                                                                                            برامج مهارات عامة-باحثين عن العمل
                                                                                        </span>
                                                                                    </label>
                                                                                </li>
                                                                                                                                                                                                                                                                                                                <li>
                                                                                    <label class="custom_check">
                                                                                        <input type="checkbox" name="categories[]" value="11" id="category-11">
                                                                                        <span class="checkmark"></span>
                                                                                        <span class="checked-title">
                                                                                            برامج مهارات عامة - الموظفين علي رأس العمل
                                                                                        </span>
                                                                                    </label>
                                                                                </li>
                                                                                                                                                                                                                                                                                                                <li>
                                                                                    <label class="custom_check">
                                                                                        <input type="checkbox" name="categories[]" value="12" id="category-12">
                                                                                        <span class="checkmark"></span>
                                                                                        <span class="checked-title">
                                                                                            الشهادات  الإحترافية
                                                                                        </span>
                                                                                    </label>
                                                                                </li>
                                                                                                                                                                                                                                                                                                                <li>
                                                                                    <label class="custom_check">
                                                                                        <input type="checkbox" name="categories[]" value="13" id="category-13">
                                                                                        <span class="checkmark"></span>
                                                                                        <span class="checked-title">
                                                                                            برامج وعد
                                                                                        </span>
                                                                                    </label>
                                                                                </li>
                                                                                                                                                                                                                        </ul>
                                                                </div>
                                                            </div>
                                                        </li>
                                                                                                    </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Fields -->
                                    <div class="collapse-card">
                                        <h4 class="card-title">
                                            <a class="" data-bs-toggle="collapse" href="{{ route('courses.index', ['locale' => $locale]) }}#field" aria-expanded="true">
                                                <img src="{{ static_asset('assets/category-icon.svg') }}" alt="icon"> المجالات                                            </a>
                                        </h4>
                                        <div id="field" class="collapse show">
                                            <div class="collapse-body">
                                                <ul class="checkbox-list">
                                                                                                                                                                        <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="fields[]" value="8" id="field-8">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        الإدارة والسياسات العامة </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="fields[]" value="10" id="field-10">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        التحول الرقمي والمعلومات </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="fields[]" value="11" id="field-11">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        المبيعات والتسويق </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="fields[]" value="13" id="field-13">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        الموارد البشرية </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                <li>
                                                                <label class="custom_check">
                                                                    <input type="checkbox" name="fields[]" value="14" id="field-14">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">
                                                                        المحاسبة </span>
                                                                </label>
                                                            </li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                <li>
                                                            <a href="javascript:void(0);" class="viewall-button-one" data-show_less="home.show less" data-show_all="عرض الكل"><span>
                                                                    عرض الكل                                                                </span></a>
                                                        </li>
                                                        <li>
                                                            <div class="view-content">
                                                                <div class="viewall-one" style="display: none;">
                                                                    <ul>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <li>
                                                                                    <label class="custom_check">
                                                                                        <input type="checkbox" name="fields[]" value="16" id="field-16">
                                                                                        <span class="checkmark"></span>
                                                                                        <span class="checked-title">
                                                                                            السياحة والضيافة
                                                                                        </span>
                                                                                    </label>
                                                                                </li>
                                                                                                                                                                                                                        </ul>
                                                                </div>
                                                            </div>
                                                        </li>
                                                                                                    </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="collapse-card">
                                        <h4 class="card-title">
                                            <a class="" data-bs-toggle="collapse" href="{{ route('courses.index', ['locale' => $locale]) }}#types" aria-expanded="true">
                                                <img src="{{ static_asset('assets/category-icon.svg') }}" alt="icon"> أنواع                                            </a>
                                        </h4>
                                        <div id="types" class="collapse show">
                                            <div class="collapse-body">
                                                <ul class="checkbox-list">
                                                    <li>
                                                        <label class="custom_check">
                                                            <input type="checkbox" name="course_types[]" value="online">
                                                            <span class="checkmark"></span>
                                                            <span class="checked-title">
                                                                عن بعد </span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="custom_check">
                                                            <input type="checkbox" name="course_types[]" value="offline">
                                                            <span class="checkmark"></span>
                                                            <span class="checked-title">
                                                                حضوري </span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="custom_check">
                                                            <input type="checkbox" name="course_types[]" value="self_learning">
                                                            <span class="checkmark"></span>
                                                            <span class="checked-title">
                                                                التعلم الذاتي </span>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /types -->


                                    <!-- Budget -->
                                    <div class="collapse-card">
                                        <h4 class="card-title">
                                            <a class="" data-bs-toggle="collapse" href="{{ route('courses.index', ['locale' => $locale]) }}#budget" aria-expanded="true">
                                                <img src="{{ static_asset('assets/money-icon.svg') }}" alt="icon">
                                                السعر                                            </a>
                                        </h4>
                                        <div id="budget" class="collapse show">
                                            <div class="collapse-body">
                                                <div class="d-flex gap-2">
                                                    <span class="text-dark">
                                                        السعر                                                        :</span>
                                                    0 : 14950 
                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                                    </svg>
                                                </div>
                                                <div class="form-group search-group">
                                                    <input type="number" value="" min="0" class="form-control" name="lower_price" placeholder="السعر الأدنى">
                                                </div>
                                                <div class="form-group search-group">
                                                    <input type="number" value="" min="0" class="form-control" name="highest_price" placeholder="السعر الأعلى">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /Budget -->
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-caret-right"></i>تصفية                                </button>
                            </div></div></div>
                        <!-- /Sidebar -->

                        <div class="col-lg-8">
                            <div class="row">
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 30 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 6 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    الشهادة المهنية الاحترافية في مؤشرات الاداء الرئيسية PC-KPIS
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_103" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">2800
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        الشهادة المهنية الاحترافية في مؤشرات الاداء الرئيسية PC-KPIS
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>30 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>6 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_103" data-course-type="online" data-id="103" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;103&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">2800
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_103" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-103" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_103">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-103">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">2800 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="103">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="103" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;2800&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_103&#39;, 103, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="103" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="103">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 15 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 3 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة محترف أعمال معتمد في السياحة والضيافة CPD-TH
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_102" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">2800
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة محترف أعمال معتمد في السياحة والضيافة CPD-TH
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>15 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>3 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_102" data-course-type="online" data-id="102" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;102&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">2800
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_102" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-102">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-102" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_102">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-102">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">2800 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="102">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="102" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;2800&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_102&#39;, 102, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="102" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="102">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 15 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 3 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    محترف الأعمال المعتمد في تحفيز الموظفين CBP-EM
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_101" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">1840
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        محترف الأعمال المعتمد في تحفيز الموظفين CBP-EM
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>15 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>3 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_101" data-course-type="online" data-id="101" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;101&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">1840
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_101" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-101">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-101" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_101">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-101">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">1840 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="101">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="101" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;1840&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_101&#39;, 101, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="101" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="101">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 15 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 3 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة محترف الأعمال المعتمد في القيادة خلال التغيير CBP-LTC
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_100" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">1595
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة محترف الأعمال المعتمد في القيادة خلال التغيير CBP-LTC
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>15 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>3 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_100" data-course-type="online" data-id="100" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;100&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">1595
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_100" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-100">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-100" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_100">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-100">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">1595 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="100">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="100" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;1595&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_100&#39;, 100, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="100" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="100">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 30 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 6 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    مدير الموارد البشرية المعتمد CPD-CHRM
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_99" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">2800
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        مدير الموارد البشرية المعتمد CPD-CHRM
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>30 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>6 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_99" data-course-type="online" data-id="99" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;99&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">2800
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_99" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-99">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-99" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_99">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-99">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">2800 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="99">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="99" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;2800&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_99&#39;, 99, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="99" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="99">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 25 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة محترف أعمال معتمد في الموارد البشرية CBP-HR
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_98" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">3165
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة محترف أعمال معتمد في الموارد البشرية CBP-HR
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>25 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_98" data-course-type="online" data-id="98" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;98&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">3165
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_98" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-98">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-98" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_98">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-98">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">3165 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="98">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="98" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;3165&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_98&#39;, 98, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="98" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="98">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/9970488b-77d5-4b11-9a26-f6ce4ec1a3f7.webp') }}" class="img-fluid" alt=" شهادة محترف أعمال معتمد في التسويق" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 15 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة محترف أعمال معتمد في التسويق (CBP - M)
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_89" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">3165
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة محترف أعمال معتمد في التسويق (CBP - M)
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>15 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_89" data-course-type="online" data-id="89" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;89&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">3165
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_89" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-89">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-89" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_89">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-89">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">3165 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="89">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="89" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;3165&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_89&#39;, 89, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="89" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="89">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/f7ea35d3-7ab3-435e-b750-39d4b15eb621.webp') }}" class="img-fluid" alt=" شهادة محترف أعمال معتمد في المبيعات" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 15 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة محترف أعمال معتمد في المبيعات (CBP - S)
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_82" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">1750
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة محترف أعمال معتمد في المبيعات (CBP - S)
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>15 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 10.4V20M12 10.4C12 8.15979 12 7.03969 11.564 6.18404C11.1805 5.43139 10.5686 4.81947 9.81596 4.43597C8.96031 4 7.84021 4 5.6 4H4.6C4.03995 4 3.75992 4 3.54601 4.10899C3.35785 4.20487 3.20487 4.35785 3.10899 4.54601C3 4.75992 3 5.03995 3 5.6V16.4C3 16.9601 3 17.2401 3.10899 17.454C3.20487 17.6422 3.35785 17.7951 3.54601 17.891C3.75992 18 4.03995 18 4.6 18H7.54668C8.08687 18 8.35696 18 8.61814 18.0466C8.84995 18.0879 9.0761 18.1563 9.29191 18.2506C9.53504 18.3567 9.75977 18.5065 10.2092 18.8062L12 20M12 10.4C12 8.15979 12 7.03969 12.436 6.18404C12.8195 5.43139 13.4314 4.81947 14.184 4.43597C15.0397 4 16.1598 4 18.4 4H19.4C19.9601 4 20.2401 4 20.454 4.10899C20.6422 4.20487 20.7951 4.35785 20.891 4.54601C21 4.75992 21 5.03995 21 5.6V16.4C21 16.9601 21 17.2401 20.891 17.454C20.7951 17.6422 20.6422 17.7951 20.454 17.891C20.2401 18 19.9601 18 19.4 18H16.4533C15.9131 18 15.643 18 15.3819 18.0466C15.15 18.0879 14.9239 18.1563 14.7081 18.2506C14.465 18.3567 14.2402 18.5065 13.7908 18.8062L12 20" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>رقم الاعتماد</h5>
                                    <p>42</p>
                                </div>
                            </li>
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_82" data-course-type="online" data-id="82" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;82&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">1750
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_82" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-82">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-82" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_82">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-82">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">1750 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="82">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="82" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;1750&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_82&#39;, 82, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="82" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="82">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/2976665c-3254-4562-8617-aff4abe9db45.webp') }}" class="img-fluid" alt=" مسؤول إدارة المخاطر المعتمد CRMO" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 30 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 4 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة مسؤول إدارة المخاطر المعتمد CRMO
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_80" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">5400
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة مسؤول إدارة المخاطر المعتمد CRMO
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>30 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>4 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_80" data-course-type="online" data-id="80" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;80&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">5400
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_80" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-80">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-80" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_80">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-80">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">5400 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="80">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="80" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;5400&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_80&#39;, 80, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="80" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="80">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/7a06dbdb-c6e2-4b9b-a607-2478275f8023.webp') }}" class="img-fluid" alt=" شهادة كومبتيا البيانات" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 40 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 8 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة كومبتيا البيانات Data + CompTIA Data+
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_79" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">10000
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة كومبتيا البيانات Data + CompTIA Data+
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>40 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>8 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_79" data-course-type="online" data-id="79" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;79&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">10000
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_79" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-79">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-79" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_79">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-79">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">10000 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="79">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="79" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;10000&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_79&#39;, 79, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="79" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="79">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/2f493696-69ff-4968-902f-3c40ab9a5e5f.webp') }}" class="img-fluid" alt=" شهادة محلل Power BI" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 40 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 20 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة محلل Power BI
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_75" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">5900
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة محلل Power BI
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>40 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>20 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_75" data-course-type="online" data-id="75" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;75&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">5900
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_75" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-75">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-75" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_75">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-75">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">5900 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="75">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="75" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;5900&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_75&#39;, 75, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="75" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="75">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/84e946f1-0fca-408b-bdde-b31426ecdde1.webp') }}" class="img-fluid" alt=" شهادة الحوكمة وإدارة المخاطر والامتثال" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 30 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 10 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    التدريب على شهادة أخصائي الحوكمة وإدارة المخاطر والالتزام GRCP
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_74" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">7300
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        التدريب على شهادة أخصائي الحوكمة وإدارة المخاطر والالتزام GRCP
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>30 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>10 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_74" data-course-type="online" data-id="74" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;74&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">7300
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_74" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-74">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-74" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_74">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-74">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">7300 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="74">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="74" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;7300&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_74&#39;, 74, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="74" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="74">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/8b3a0b07-d671-4b0e-b72a-7e3eff5e6ce2.webp') }}" class="img-fluid" alt=" شهادة اعتماد مدير قطاع الضيافة" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 18 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 3 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة اعتماد مدير قطاع الضيافة (CHA)
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_73" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">14950
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة اعتماد مدير قطاع الضيافة (CHA)
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>18 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>3 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_73" data-course-type="online" data-id="73" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;73&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">14950
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_73" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-73">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-73" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_73">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-73">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">14950 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="73">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="73" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;14950&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_73&#39;, 73, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="73" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="73">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/0c59ef5b-bf36-461c-b251-90ad61d48846.webp') }}" class="img-fluid" alt=" شهادة اعتماد خدمة الضيوف الذهبية المهنية" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 4 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 1 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة اعتماد خدمة الضيوف الذهبية المهنية (CGSP)
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_72" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">2390
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة اعتماد خدمة الضيوف الذهبية المهنية (CGSP)
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>4 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>1 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_72" data-course-type="online" data-id="72" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;72&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">2390
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_72" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-72">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-72" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_72">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-72">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">2390 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="72">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="72" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;2390&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_72&#39;, 72, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="72" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="72">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/a0d1ecc3-b846-4af4-afb6-f00aff97c8d9.webp') }}" class="img-fluid" alt=" شهادة محترف أعمال معتمد في خدمة العملاء" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 15 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة محترف أعمال معتمد في  خدمة العملاء  CBP (IBTA)
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_70" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">1840
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة محترف أعمال معتمد في  خدمة العملاء  CBP (IBTA)
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>15 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_70" data-course-type="online" data-id="70" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;70&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">1840
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_70" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-70">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-70" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_70">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-70">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">1840 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="70">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="70" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;1840&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_70&#39;, 70, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="70" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="70">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/0be7d70f-8046-43a3-b9a6-0f36eb8fa99a.webp') }}" class="img-fluid" alt=" شهادة مدير سلسلة التوريد الدولية المعتمد CISCM" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 30 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة مدير سلسلة التوريد الدولية المعتمد CISCM
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_69" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">8000
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة مدير سلسلة التوريد الدولية المعتمد CISCM
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>30 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_69" data-course-type="online" data-id="69" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;69&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">8000
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_69" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-69">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-69" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_69">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-69">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">8000 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="69">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="69" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;8000&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_69&#39;, 69, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="69" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="69">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/18223653-8beb-487e-a913-22d91e63d90f.webp') }}" class="img-fluid" alt=" الشهادة الاحترافية المعتمدة في سلسلة التوريد الدولية" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 40 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    الشهادة الاحترافية المعتمدة في سلسلة التوريد الدولية CISCP
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_68" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">8000
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        الشهادة الاحترافية المعتمدة في سلسلة التوريد الدولية CISCP
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>40 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_68" data-course-type="online" data-id="68" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;68&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">8000
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_68" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-68">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-68" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_68">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-68">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">8000 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="68">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="68" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;8000&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_68&#39;, 68, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="68" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="68">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/acc9925f-2182-4994-bb4b-4e6507d3d575.webp') }}" class="img-fluid" alt=" الشهادة الاحترافية المعتمدة في المشتريات CIPP" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 40 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 3 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    الشهادة الاحترافية المعتمدة في المشتريات CIPP
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_66" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">8000
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        الشهادة الاحترافية المعتمدة في المشتريات CIPP
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>40 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>3 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_66" data-course-type="online" data-id="66" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;66&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">8000
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_66" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-66">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-66" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_66">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-66">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">8000 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="66">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="66" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;8000&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_66&#39;, 66, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="66" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="66">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/b19ef546-4d7f-4ca7-87b5-c413193499dc.webp') }}" class="img-fluid" alt=" الشهادة الدولية في اللوجستيات والنقل" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 240 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 40 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    الشهادة الدولية في اللوجستيات والنقل (CILT (CILT
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_64" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">8400
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        الشهادة الدولية في اللوجستيات والنقل (CILT (CILT
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>240 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>40 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_64" data-course-type="online" data-id="64" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;64&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">8400
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_64" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-64">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-64" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_64">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-64">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">8400 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="64">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="64" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;8400&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_64&#39;, 64, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="64" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="64">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/2c339d02-9016-4369-9d5b-6745107bffe4.webp') }}" class="img-fluid" alt=" شهادة فني المحاسبة" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 40 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 7 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة فني المحاسبة SOCPA CAT
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_63" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">7130
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة فني المحاسبة SOCPA CAT
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>40 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>7 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_63" data-course-type="online" data-id="63" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;63&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">7130
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_63" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-63">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-63" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_63">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-63">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">7130 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="63">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="63" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;7130&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_63&#39;, 63, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="63" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="63">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/f3fca8f1-4751-4327-82e1-ec72893f0102.webp') }}" class="img-fluid" alt=" ممارس الذكاء الاصطناعي المعتمد" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 40 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('course-ai-practitioner.html') }}">
                    ممارس الذكاء الاصطناعي المعتمد من AWS
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_62" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">3000
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('course-ai-practitioner.html') }}">
                        ممارس الذكاء الاصطناعي المعتمد من AWS
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>40 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_62" data-course-type="online" data-id="62" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;62&#39;, this)" data-loaded="true">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">3000
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_62" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-62">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-62" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_62">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-62">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">3000 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="62">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="62" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;3000&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_62&#39;, 62, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="62" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="62">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/7ff354e1-d054-481d-bc2d-929031778cc4.webp') }}" class="img-fluid" alt=" الشهادة الدولية للحاسب والإنترنت" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 60 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 30 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    الشهادة الدولية للحاسب والإنترنت (IC3)
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_61" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">3000
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        الشهادة الدولية للحاسب والإنترنت (IC3)
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>60 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>30 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_61" data-course-type="online" data-id="61" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;61&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">3000
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_61" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-61">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-61" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_61">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-61">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">3000 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="61">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="61" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;3000&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_61&#39;, 61, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="61" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="61">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/ade251ed-eab6-4baa-b579-ed2b9e9d4e56.webp') }}" class="img-fluid" alt=" شهادة محترف Adobe في التصميم المرئي ACP" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 20 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة محترف Adobe في التصميم المرئي ACP
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_60" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">4700
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة محترف Adobe في التصميم المرئي ACP
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>20 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_60" data-course-type="online" data-id="60" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;60&#39;, this)" data-loaded="true">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">4700
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_60" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-60">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-60" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_60">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-60">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">4700 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="60">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="60" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;4700&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_60&#39;, 60, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="60" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="60">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/f46e76b0-3186-4d21-b6e5-9b904d2d4d71.webp') }}" class="img-fluid" alt=" شهادة مكتبة البنية التحتية المعلوماتية" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 20 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 4 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة مكتبة البنية التحتية المعلوماتية (AXELOS (ITIL
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_59" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">5300
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة مكتبة البنية التحتية المعلوماتية (AXELOS (ITIL
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>20 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>4 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_59" data-course-type="online" data-id="59" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;59&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">5300
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_59" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-59">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-59" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_59">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-59">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">5300 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="59">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="59" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;5300&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_59&#39;, 59, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="59" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="59">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/106e193c-6608-4c05-b44b-2b5442029240.webp') }}" class="img-fluid" alt=" شهادة CompTIA أساسيات الحماية والأمن السيبراني" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 60 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 10 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة CompTIA أساسيات الحماية والأمن السيبراني
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_58" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">11525
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة CompTIA أساسيات الحماية والأمن السيبراني
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>60 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>10 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_58" data-course-type="online" data-id="58" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;58&#39;, this)" data-loaded="true">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">11525
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_58" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-58">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-58" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_58">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-58">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">11525 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="58">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="58" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;11525&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_58&#39;, 58, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="58" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="58">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/c22b0d8a-a148-4b8d-80c8-a9ac1a029240.webp') }}" class="img-fluid" alt=" شهادة CompTIA محلل الأمن السيبراني" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 60 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 20 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة CompTIA محلل الأمن السيبراني (CySA)
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_57" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">13700
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة CompTIA محلل الأمن السيبراني (CySA)
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>60 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>20 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_57" data-course-type="online" data-id="57" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;57&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">13700
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_57" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-57">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-57" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_57">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-57">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">13700 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="57">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="57" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;13700&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_57&#39;, 57, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="57" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="57">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ static_asset('assets/5d38d029-8d45-48eb-8864-7486be5cd329.webp') }}" class="img-fluid" alt=" شهادة المشارك المهنية في الموارد البشرية" loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                الشهادات  الإحترافية
            </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 35 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 5 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    شهادة المشارك المهنية في الموارد البشرية (APHRI)
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_55" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price">5500
                                                            <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
        </svg>
                                                                            </span>
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        شهادة المشارك المهنية في الموارد البشرية (APHRI)
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>35 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>5 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_55" data-course-type="online" data-id="55" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;55&#39;, this)" data-loaded="true">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price">5500
                                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: var(--primary);">
            <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="var(--primary)"></path>
        </svg>
                                                                    </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                
                                                        <div id="course_55" class="w-100">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="cart-form-55">
                                    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                                    <div class="modal-body row d-none">
                                                                                    <div class="form-check col-auto m-auto d-none">
                                                <input class="form-check-input" type="radio" name="course_type" checked="" id="course_type_online-55" value="online">
                                                <input class="form-check-input" type="hidden" name="training_id" id="course_training_id_55">
                                                <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-55">
                                                    <span class="course-type align-content-center">عن بعد</span>
                                                    <span class="course-price course-price-modal">5500 </span>
                                                </label>
                                            </div>
                                                                            </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="course_id" value="55">


                                        <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="55" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;5500&quot;}">

                                            <span class="btn-text">
                                                                                                                                                            إضافة  للعربة
                                                                                                        <span class="btn-add-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M9 8H21" stroke="#8f8f8f" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                                                            </span>

                                            <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                                        </button>
                                                                                    <a class="btn btn-primary d-flex gap-1 buy-btn" href="javascript:void(0);" onclick="openBuyNowLazy(&#39;buyNowModal_55&#39;, 55, &#39;online&#39;)" title="شراء الان">
                                                شراء الان
                                                <span class="btn-add-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                                        <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                        <line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                                    </svg>
                                                </span>

                                            </a>
                                                                                <div class="fav-selection position-relative">


                                            <a href="javascript:void(0);" class="fav-icon                                                     makeWishlist                                                     " data-course_id="55" title="إضافة إلى القائمة المفضلة">
                                                <svg class="heart-icon " width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                                    <path class="heart-outline" d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z" fill="#8f8f8f"></path>

                                                    <path class="heart-filled" d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z" fill="var(--primary)"></path>
                                                </svg>

                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
                        <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                        <input type="hidden" name="course_id" value="55">
                        <input type="hidden" name="type" id="free_course_type">
                    </form>


                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                برامج وعد
            </a>
                            <a href="javascript:void(0)" class="finished courses">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16">
                        <path fill="currentColor" fill-rule="evenodd" d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002ZM8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22V3.75ZM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0Zm-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0v-3.5Z" clip-rule="evenodd"></path>
                    </svg>
                    انتهى موعد التسجيل
                </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 5 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 1 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    ورشة عمل الابتكار والإبداع في العمل المؤسسي
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_51" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price fw-bold">
                             مجانية
                        </span>
                                        
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        ورشة عمل الابتكار والإبداع في العمل المؤسسي
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>5 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>1 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_51" data-course-type="online" data-id="51" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;51&#39;, this)">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price text-success fw-bold">
                                 مجانية
                            </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                                    <a href="./client-request?course=%D9%88%D8%B1%D8%B4%D8%A9-%D8%B9%D9%85%D9%84-%D8%A7%D9%84%D8%A7%D8%A8%D8%AA%D9%83%D8%A7%D8%B1-%D9%88%D8%A7%D9%84%D8%A5%D8%A8%D8%AF%D8%A7%D8%B9-%D9%81%D9%8A-%D8%A7%D9%84%D8%B9%D9%85%D9%84-%D8%A7%D9%84%D9%85%D8%A4%D8%B3%D8%B3%D9%8A" class="btn btn-outline-primary w-100 mt-2">
                        <i class="feather-heart me-2"></i>سجل بياناتك للتواصل                    </a>
                
                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                برامج وعد
            </a>
                            <a href="javascript:void(0)" class="finished courses">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16">
                        <path fill="currentColor" fill-rule="evenodd" d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002ZM8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22V3.75ZM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0Zm-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0v-3.5Z" clip-rule="evenodd"></path>
                    </svg>
                    انتهى موعد التسجيل
                </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 5 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 1 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    ورشة عمل التحول الرقمي: رؤية وأهداف ترسم المستقبل
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_50" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price fw-bold">
                             مجانية
                        </span>
                                        
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        ورشة عمل التحول الرقمي: رؤية وأهداف ترسم المستقبل
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>5 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>1 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_50" data-course-type="online" data-id="50" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;50&#39;, this)" data-loaded="true">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price text-success fw-bold">
                                 مجانية
                            </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                                    <a href="./client-request?course=%D9%88%D8%B1%D8%B4%D8%A9-%D8%B9%D9%85%D9%84-%D8%A7%D9%84%D8%AA%D8%AD%D9%88%D9%84-%D8%A7%D9%84%D8%B1%D9%82%D9%85%D9%8A" class="btn btn-outline-primary w-100 mt-2">
                        <i class="feather-heart me-2"></i>سجل بياناتك للتواصل                    </a>
                
                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                                                    <!-- Service List -->
                                    <div class="col-xl-6 col-md-6">
                                        <div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    
    <div class="gigs-img">
        <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
            <img src="{{ default_poster_url() }}" class="img-fluid" alt=" " loading="lazy">
        </a>
        
        

    </div>
    <div class="gigs-content">

        <div class="gigs-info">
            <a href="{{ legacy_page('./course-ai-practitioner.html') }}" class="badge bg-primary-light cardBadge">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                </svg>
                برامج وعد
            </a>
                            <a href="javascript:void(0)" class="finished courses">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16">
                        <path fill="currentColor" fill-rule="evenodd" d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002ZM8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22V3.75ZM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0Zm-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0v-3.5Z" clip-rule="evenodd"></path>
                    </svg>
                    انتهى موعد التسجيل
                </a>
                        
            <div class="gigs-card-footer justify-content-start gap-2 mb-0">

                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                        </svg>
                        <span> 5 ساعة </span>
                    </div>
                                                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                            </g>
                        </svg>
                        <span> 1 يوم </span>
                    </div>
                            </div>

        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                    ورشة عمل إدارة التغيير في المنظمات
                </a>
            </h3>
        </div>


        <div class="gigs-card-footer justify-content-start gap-3">
                                                                        <p class="badge d-flex flex-column " id="flag_type_select_online_49" data-course_type="online">
                                        <span class="course_type">عن بعد</span>
                                            <span class="course_price fw-bold">
                             مجانية
                        </span>
                                        
                    </p>
                                    </div>

        <!-- /Sidebar -->
        
    </div>
    <div class="card-tooltip-overlay">

    <div class="tooltip-content">

        <div class="gigs-content">

            <div class="gigs-title">
                <h3>
                    <a href="{{ legacy_page('./course-ai-practitioner.html') }}">
                        ورشة عمل إدارة التغيير في المنظمات
                    </a>
                </h3>
            </div>

            <div class="card p-4 sticky-top">

                <div class="card-content main-card">
                    <ul>

                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الساعات</h5>
                                    <p>5 ساعة</p>
                                </div>
                            </li>
                        
                        
                                                    <li>
                                <div class="sidbar-icon">
                                    <svg fill="#2b2b2b" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 31.475 31.475" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path d="M29.246,19.316c-0.303-0.336-3.152-8.432-3.152-8.432l-2.691-0.389l-1.607,1.927l-2.022-1.84l-1.875-0.166l-6.058-5.165 c0.323-0.249,0.522-0.55,0.571-0.886c0.076-0.523-0.188-1.104-0.791-1.727l-0.153-0.16l-0.154,0.16 c-0.129,0.132-0.256,0.262-0.381,0.388C10.625,2.754,9.914,2.224,8.997,2.26L8.877,2.266L8.818,2.37 c-1.062,1.88-5.009,4.568-5.47,4.878C3.17,7.312,2.976,7.434,2.768,7.649C2.018,8.426,2.14,9.61,2.572,10.378 c0.371,0.662,0.945,1.04,1.553,1.04c0.105,0,0.213-0.012,0.32-0.035c0.298-0.065,0.526-0.216,0.702-0.418 c0.293-0.301,1.757-1.79,3.158-3.022C8.45,8.115,8.839,8.609,8.997,9.129c0.199,0.643,0.285,1.631,0.285,1.631l0.32-0.697 l0.465,0.146c0,0-0.068-0.898-0.336-1.418c-0.269-0.52-0.644-1.055-0.644-1.055s0.692,0.35,1.091,0.785 c0.396,0.438,0.757,1.32,0.757,1.32l0.264-0.515l0.702-0.165l5.983,5.755c-0.059,3.718-0.217,12.572-0.254,13.246 c-0.052,0.939-2.75,1.49-2.241,2.398c0.196,0.316,1.544,0.323,3.273,0.285v0.629h2.633V30.79c0.079-0.001,0.158-0.002,0.237-0.003 c0.282-0.002,0.564-0.003,0.845-0.002v0.689h2.636v-0.664c1.472,0.014,2.554-0.002,2.679-0.186 c0.421-0.616-1.826-1.378-1.859-2.322c-0.022-0.688-0.275-7.896-0.414-11.797l0.021-0.061l0.972,4.02c0,0,0.3-0.105,0.703-0.254 l0.27,0.7l1.633-0.625l-0.271-0.703C29.061,19.451,29.274,19.346,29.246,19.316z M5.333,7.589L5.136,7.635l0.033,0.2 c0.141,0.79,0.265,2.893-0.817,3.131c-0.631,0.141-1.143-0.318-1.407-0.796C2.588,9.535,2.478,8.567,3.077,7.946 c0.3-0.312,0.572-0.399,0.812-0.264c0.156,0.089,0.29,0.257,0.389,0.45C4.121,8.285,3.792,8.59,3.189,9.104L3.093,9.186L3.117,9.31 c0.16,0.796,0.596,0.896,0.775,0.901c0.419,0.01,0.815-0.375,0.979-0.971c0.141-0.507-0.13-1.564-0.772-1.932 C4.083,7.299,4.069,7.29,4.052,7.284c0.56-0.388,1.493-1.063,2.434-1.843C6.867,5.513,7.367,5.674,7.68,6.039 C6.016,7.397,5.343,7.587,5.333,7.589z M4.437,8.573c0.051,0.214,0.06,0.417,0.021,0.554c-0.116,0.43-0.365,0.657-0.545,0.657 c-0.003,0-0.005,0-0.008-0.001c-0.142-0.005-0.265-0.166-0.34-0.437C3.983,8.989,4.259,8.738,4.437,8.573z M5.609,9.895 C5.751,9.133,5.668,8.3,5.621,7.939C5.934,7.806,6.65,7.431,7.91,6.406c0.191,0.388,0.276,0.766,0.312,1.041 C7.28,8.263,6.285,9.225,5.609,9.895z M7.499,4.554C8.171,3.928,8.769,3.28,9.127,2.688c0.69,0.017,1.247,0.424,1.499,0.646 c-0.743,0.745-1.397,1.36-1.97,1.869C8.286,4.742,7.85,4.59,7.499,4.554z M8.903,5.558c0.72-0.641,1.567-1.444,2.559-2.459 c0.398,0.456,0.575,0.859,0.525,1.204c-0.042,0.289-0.249,0.541-0.615,0.75L11.23,4.51L9.762,5.463l0.16,0.619 C9.72,6.235,9.506,6.397,9.288,6.569C9.285,6.372,9.228,6.088,8.903,5.558z M10.499,7.877C10.247,7.7,9.553,7.252,9.362,7.057 C9.427,7.005,9.49,6.956,9.554,6.906l1.418,1.364C10.77,8.094,10.587,7.941,10.499,7.877z M17.355,0.888h-1.947V0H28.24v0.889 h-0.578v2.05h0.315v1.734h-1.476V2.939h0.476V0.888H26.03V3.78l-0.275,0.086c0.201,0.502,0.318,1.047,0.318,1.62 c0,2.405-1.951,4.355-4.355,4.355c-2.403,0-4.354-1.95-4.354-4.355c0-0.57,0.117-1.112,0.316-1.611L17.355,3.78V0.888z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between w-100">
                                    <h5>عدد الأيام</h5>
                                    <p>1 يوم</p>
                                </div>
                            </li>
                        
                        
                        
                    </ul>
                </div>

            </div>


            
                        <div class="gigs-card-footer justify-content-start gap-2">
                                                                                        <p class="badge d-flex flex-column selected-course course-type-item" id="flag_type_select_online_49" data-course-type="online" data-id="49" style="cursor: pointer;" onclick="selectCourseType(&#39;online&#39;, &#39;49&#39;, this)" data-loaded="true">
                                                    <span class="course_type">عن بعد</span>
                                                    <span class="course_price text-success fw-bold">
                                 مجانية
                            </span>
                                                </p>
                                                </div>

            
            <!-- /Sidebar -->
            <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                                    <a href="./client-request?course=%D9%88%D8%B1%D8%B4%D8%A9-%D8%B9%D9%85%D9%84-%D8%A5%D8%AF%D8%A7%D8%B1%D8%A9-%D8%A7%D9%84%D8%AA%D8%BA%D9%8A%D9%8A%D8%B1-%D9%81%D9%8A-%D8%A7%D9%84%D9%85%D9%86%D8%B8%D9%85%D8%A7%D8%AA" class="btn btn-outline-primary w-100 mt-2">
                        <i class="feather-heart me-2"></i>سجل بياناتك للتواصل                    </a>
                
                
            </div>

        </div>

    </div>

</div>


</div>
                                    </div>
                                    <!-- /Service List -->
                                
                                <div class="col-md-12">
                                    <!-- Pagination -->
                                    <div class="pagination">
        <ul>
            
            <li>
                <a href="javascript:void(0);" class="disabled">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            </li>
    
                
            
                                                                                                                                                
            
                                                        <li>
                    <a href="courses.html?page=1" class="active">1</a>
                </li>
                                                                                                <li>
                    <a href="courses.html?page=2" class="">2</a>
                </li>
                                
            
            <li>
                <a href="courses.html?page=2" class="next">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </div>
                                        <!-- /Pagination -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div></form>
                <!-- /Service Details -->
            
        </div>
    </div>
    <!-- /Page Content -->

    <!-- cours prices modals-->
            <div class="modal fade" id="course_103" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_103Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_103Label">الشهادة المهنية الاحترافية في مؤشرات الاداء الرئيسية PC-KPIS</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-103">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-103" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-103">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">2800 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="103">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="103" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;2800&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="103">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_102" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_102Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_102Label">شهادة محترف أعمال معتمد في السياحة والضيافة CPD-TH</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-102">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-102" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-102">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">2800 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="102">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="102" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;2800&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="102">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_101" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_101Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_101Label">محترف الأعمال المعتمد في تحفيز الموظفين CBP-EM</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-101">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-101" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-101">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">1840 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="101">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="101" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;1840&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="101">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_100" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_100Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_100Label">شهادة محترف الأعمال المعتمد في القيادة خلال التغيير CBP-LTC</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-100">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-100" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-100">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">1595 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="100">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="100" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;1595&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="100">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_99" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_99Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_99Label">مدير الموارد البشرية المعتمد CPD-CHRM</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-99">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-99" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-99">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">2800 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="99">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="99" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;2800&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="99">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_98" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_98Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_98Label">شهادة محترف أعمال معتمد في الموارد البشرية CBP-HR</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-98">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-98" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-98">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">3165 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="98">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="98" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;3165&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="98">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_89" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_89Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_89Label">شهادة محترف أعمال معتمد في التسويق (CBP - M)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-89">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-89" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-89">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">3165 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="89">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="89" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;3165&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="89">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_82" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_82Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_82Label">شهادة محترف أعمال معتمد في المبيعات (CBP - S)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-82">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-82" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-82">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">1750 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="82">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="82" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;1750&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="82">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_80" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_80Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_80Label">شهادة مسؤول إدارة المخاطر المعتمد CRMO</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-80">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-80" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-80">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">5400 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="80">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="80" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;5400&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="80">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_79" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_79Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_79Label">شهادة كومبتيا البيانات Data + CompTIA Data+</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-79">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-79" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-79">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">10000 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="79">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="79" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;10000&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="79">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_75" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_75Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_75Label">شهادة محلل Power BI</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-75">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-75" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-75">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">5900 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="75">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="75" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;5900&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="75">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_74" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_74Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_74Label">التدريب على شهادة أخصائي الحوكمة وإدارة المخاطر والالتزام GRCP</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-74">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-74" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-74">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">7300 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="74">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="74" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;7300&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="74">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_73" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_73Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_73Label">شهادة اعتماد مدير قطاع الضيافة (CHA)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-73">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-73" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-73">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">14950 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="73">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="73" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;14950&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="73">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_72" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_72Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_72Label">شهادة اعتماد خدمة الضيوف الذهبية المهنية (CGSP)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-72">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-72" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-72">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">2390 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="72">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="72" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;2390&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="72">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_70" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_70Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_70Label">شهادة محترف أعمال معتمد في  خدمة العملاء  CBP (IBTA)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-70">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-70" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-70">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">1840 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="70">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="70" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;1840&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="70">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_69" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_69Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_69Label">شهادة مدير سلسلة التوريد الدولية المعتمد CISCM</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-69">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-69" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-69">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">8000 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="69">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="69" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;8000&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="69">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_68" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_68Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_68Label">الشهادة الاحترافية المعتمدة في سلسلة التوريد الدولية CISCP</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-68">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-68" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-68">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">8000 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="68">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="68" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;8000&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="68">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_66" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_66Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_66Label">الشهادة الاحترافية المعتمدة في المشتريات CIPP</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-66">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-66" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-66">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">8000 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="66">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="66" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;8000&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="66">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_64" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_64Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_64Label">الشهادة الدولية في اللوجستيات والنقل (CILT (CILT</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-64">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-64" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-64">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">8400 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="64">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="64" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;8400&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="64">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_63" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_63Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_63Label">شهادة فني المحاسبة SOCPA CAT</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-63">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-63" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-63">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">7130 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="63">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="63" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;7130&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="63">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_62" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_62Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_62Label">ممارس الذكاء الاصطناعي المعتمد من AWS</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-62">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-62" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-62">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">3000 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="62">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="62" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;3000&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="62">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_61" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_61Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_61Label">الشهادة الدولية للحاسب والإنترنت (IC3)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-61">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-61" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-61">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">3000 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="61">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="61" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;3000&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="61">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_60" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_60Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_60Label">شهادة محترف Adobe في التصميم المرئي ACP</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-60">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-60" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-60">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">4700 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="60">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="60" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;4700&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="60">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_59" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_59Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_59Label">شهادة مكتبة البنية التحتية المعلوماتية (AXELOS (ITIL</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-59">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-59" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-59">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">5300 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="59">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="59" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;5300&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="59">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_58" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_58Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_58Label">شهادة CompTIA أساسيات الحماية والأمن السيبراني</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-58">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-58" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-58">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">11525 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="58">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="58" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;11525&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="58">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_57" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_57Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_57Label">شهادة CompTIA محلل الأمن السيبراني (CySA)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-57">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-57" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-57">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">13700 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="57">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="57" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;13700&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="57">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_55" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_55Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_55Label">شهادة المشارك المهنية في الموارد البشرية (APHRI)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-55">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-55" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-55">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">5500 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="55">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="55" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;5500&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="55">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_51" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_51Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_51Label">ورشة عمل الابتكار والإبداع في العمل المؤسسي</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-51">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-51" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-51">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">0 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="51">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="51" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;0&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="51">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_50" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_50Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_50Label">ورشة عمل التحول الرقمي: رؤية وأهداف ترسم المستقبل</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-50">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-50" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-50">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">0 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="50">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="50" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;0&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="50">
    <input type="hidden" name="type" id="free_course_type">
</form>

            <div class="modal fade" id="course_49" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="course_49Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="course_49Label">ورشة عمل إدارة التغيير في المنظمات</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cart-form-49">
                <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">                <div class="modal-body row">
                                            <div class="form-check col-auto m-auto">
                            <input class="form-check-input" type="radio" name="course_type" id="course_type_online-49" value="online">
                            <label class="form-check-label d-flex justify-content-between align-items-center gap-2 w-100 mb-0" for="course_type_online-49">
                                <span class="course-type align-content-center">عن بعد</span>
                                <span class="course-price course-price-modal">0 
                                     <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="styles__icon--f9b3f" style="width: 20px; height: 20px; color: #fd9239;">
                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </label>
                        </div>
                                    </div>
                <div class="modal-footer justify-content-start">
                    <input type="hidden" name="course_id" value="49">
                    <button type="button" class="btn btn-primary Add-to-Cart" data-course_id="49" data-in_cart="false" data-prices="{&quot;online&quot;:&quot;0&quot;}">
                        <span class="btn-text">
                            إضافة  للعربة
                        </span>
                        <span class="spinner-border spinner-border-sm d-none ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="freeCheckoutForm" method="POST" action="#" onsubmit="return false;" style="display: none;">
    <input type="hidden" name="_token" value="ZXdZr83HMJ7XuQELqUeoinmMWevyjcMWDXQnIyEM" autocomplete="off">    <input type="hidden" name="course_id" value="49">
    <input type="hidden" name="type" id="free_course_type">
</form>

    


            <a href="tel:+966543406744" class="float-call">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M21.97 18.33C21.97 18.69 21.89 19.06 21.72 19.42C21.55 19.78 21.33 20.12 21.04 20.44C20.55 20.98 20.01 21.37 19.4 21.62C18.8 21.87 18.15 22 17.45 22C16.43 22 15.34 21.76 14.19 21.27C13.04 20.78 11.89 20.12 10.75 19.29C9.6 18.45 8.51 17.52 7.47 16.49C6.44 15.45 5.51 14.36 4.68 13.22C3.86 12.08 3.2 10.94 2.72 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C11.27 13.75 11.79 14.24 12.32 14.69C12.84 15.13 13.27 15.43 13.61 15.61C13.66 15.63 13.72 15.66 13.79 15.69C13.87 15.72 13.95 15.73 14.04 15.73C14.21 15.73 14.34 15.67 14.45 15.56L15.21 14.81C15.46 14.56 15.7 14.37 15.93 14.25C16.16 14.11 16.39 14.04 16.64 14.04C16.83 14.04 17.03 14.08 17.25 14.17C17.47 14.26 17.7 14.39 17.95 14.56L21.26 16.91C21.52 17.09 21.7 17.3 21.81 17.55C21.91 17.8 21.97 18.05 21.97 18.33Z" stroke="#ffffff" stroke-width="1.5" stroke-miterlimit="10"></path> </g></svg>
        </a>