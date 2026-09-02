<!-- /ramzy -->


        <!-- Hero Section -->

        <section class="hero-section">
            <div class="home-slider owl-carousel owl-rtl owl-loaded owl-drag">

                <div class="owl-stage-outer">
                    <div class="owl-stage"
                        style="transform: translate3d(0px, 0px, 0px); transition: all; width: 1937px;">
                        <div class="owl-item active" style="width: 1915px; margin-left: 22px;">
                            <div class="home-item">

                                <img class="sliderImg" src="{{ static_asset(platform_campus_path('aerial')) }}" alt="{{ platform_org() }}">

                                <div class="container">
                                    <div class="row align-items-center">
                                        <div class="col-xl-12 mx-auto col-lg-12">
                                            <div class="banner-content aos aos-init aos-animate" data-aos="fade-up">
                                                <div class="banner-head">
                                                    <h1> مركز التعلم المستمر</h1>
                                                    <p></p>
                                                    <h2 style="text-align: center;"><span
                                                            style="font-size: 18pt; color: rgb(194, 224, 244);">للدورات
                                                            التدريبية</span></h2>
                                                    <h2 style="text-align: center;"><span
                                                            style="font-size: 18pt; color: rgb(194, 224, 244);">بمعهد
                                                            البحوث والدراسات الاستشارية بالجامعة العربية المفتوحة</span></h2>
                                                    <p></p>
                                                </div>
                                                <div class="banner-form">
                                                   <form action="{{ route('courses.index', ['locale' => app()->getLocale()]) }}" method="get">
                                                       <div class="banner-search-list">
                                                           <div class="input-block">
                                                           <label>  المجالات  </label>
                                                           <select class="select" name="fields[]">
                                                               <option value="">الكل </option>
                                                               @foreach ($popularFields ?? collect() as $field)
                                                                   <option value="{{ $field->id }}">{{ $field->displayTitle() }}</option>
                                                               @endforeach
                                                           </select>
                                                           </div>
                                                           <div class="input-block border-0">
                                                               <label> بحث </label>
                                                               <input type="search" name="q" class="form-control" placeholder="ابحث عن دورة…" value="">
                                                           </div>
                                                       </div>
                                                       <div class="input-block-btn">
                                                           <button class="btn btn-primary" type="submit">
                                                               <i class="fas fa-magnifying-glass"></i> بحث
                                                           </button>
                                                       </div>
                                                   </form>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="owl-nav disabled"><button type="button" role="presentation"
                        class="owl-prev"></button><button type="button" role="presentation" class="owl-next"></button>
                </div>
                <div class="owl-dots disabled"></div>
            </div>
        </section>
        <!-- /Hero Section -->


        @include('partials.catalog.home-popular-fields', ['fields' => $popularFields ?? collect()])
        <!-- /Popular -->


        <!-- Explore Gigs -->
        @include('partials.catalog.home-featured-courses', ['courses' => $professionalCertificates ?? collect()])
        <!-- /Explore Gigs -->

        @include('partials.catalog.home-diplomas', ['diplomas' => $diplomas ?? collect()])

        <!-- Explore Gigs -->
        <section id="section-fellowships" class="explore-gigs-section">
            <div class="container">
                <div class="section-head d-flex">
                    <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                        <h2> الزمالات المهنية</h2>
                    </div>
                </div>
                <div class="dashboard-tab">
                    <div class="row aos aos-init aos-animate" data-aos="fade-up" data-aos-delay="500">
                        <div class="col-md-12">
                            <div class="gigs-card-slider owl-carousel owl-rtl owl-loaded owl-drag">


                                <div class="owl-stage-outer">
                                    <div class="owl-stage"
                                        style="transform: translate3d(0px, 0px, 0px); transition: all; width: 2200px;">
                                        <div class="owl-item active" style="width: 416px; margin-left: 24px;">
                                            <div class="gigs-grid">
                                                <div class="gigs-img">
                                                    <a
                                                        href="{{ legacy_page('ar/fellowship/%D8%A7%D9%84%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D8%AA%D9%82%D8%AF%D9%85%D8%A9-%D9%84%D8%B1%D9%8A%D8%A7%D8%AF%D8%A9-%D8%A7%D9%84%D8%A3%D8%B9%D9%85%D8%A7%D9%84.html') }}">
                                                        <img src="{{ static_asset('assets/1856748897145287.webp') }}" class="img-fluid"
                                                            alt=" ">
                                                    </a>
                                                    <div class="card-overlay-badge">
                                                    </div>
                                                    <a href="javascript:void(0)" class="finished soon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                            viewBox="0 0 16 16">
                                                            <path fill="currentColor" fill-rule="evenodd"
                                                                d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002ZM8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22V3.75ZM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0Zm-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0v-3.5Z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>

                                                        قريباً </a>
                                                </div>

                                                <div class="gigs-content">

                                                    <div class="gigs-info level-badge position-relative">
                                                        <span class="badge bg-primary-light">
                                                            الزمالة المهنية
                                                        </span>
                                                    </div>
                                                    <div class="gigs-title">
                                                        <h3 class="fw-bold">
                                                            <a href="{{ legacy_page('ar/fellowship/%D8%A7%D9%84%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D8%AA%D9%82%D8%AF%D9%85%D8%A9-%D9%84%D8%B1%D9%8A%D8%A7%D8%AF%D8%A9-%D8%A7%D9%84%D8%A3%D8%B9%D9%85%D8%A7%D9%84.html') }}"
                                                                class="fw-bold">
                                                                الزمالة المتقدمة لريادة الأعمال
                                                            </a>
                                                        </h3>

                                                    </div>
                                                    <div class="card-overlay-badge">
                                                    </div>

                                                    <div class="">
                                                        <div class="card-content main-card">

                                                            <ul>

                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 10.4V20M12 10.4C12 8.15979 12 7.03969 11.564 6.18404C11.1805 5.43139 10.5686 4.81947 9.81596 4.43597C8.96031 4 7.84021 4 5.6 4H4.6C4.03995 4 3.75992 4 3.54601 4.10899C3.35785 4.20487 3.20487 4.35785 3.10899 4.54601C3 4.75992 3 5.03995 3 5.6V16.4C3 16.9601 3 17.2401 3.10899 17.454C3.20487 17.6422 3.35785 17.7951 3.54601 17.891C3.75992 18 4.03995 18 4.6 18H7.54668C8.08687 18 8.35696 18 8.61814 18.0466C8.84995 18.0879 9.0761 18.1563 9.29191 18.2506C9.53504 18.3567 9.75977 18.5065 10.2092 18.8062L12 20M12 10.4C12 8.15979 12 7.03969 12.436 6.18404C12.8195 5.43139 13.4314 4.81947 14.184 4.43597C15.0397 4 16.1598 4 18.4 4H19.4C19.9601 4 20.2401 4 20.454 4.10899C20.6422 4.20487 20.7951 4.35785 20.891 4.54601C21 4.75992 21 5.03995 21 5.6V16.4C21 16.9601 21 17.2401 20.891 17.454C20.7951 17.6422 20.6422 17.7951 20.454 17.891C20.2401 18 19.9601 18 19.4 18H16.4533C15.9131 18 15.643 18 15.3819 18.0466C15.15 18.0879 14.9239 18.1563 14.7081 18.2506C14.465 18.3567 14.2402 18.5065 13.7908 18.8062L12 20"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            آلية الدراسة
                                                                        </h5>
                                                                        <p>
                                                                            التعلم المدمج
                                                                        </p>
                                                                    </div>
                                                                </li>


                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            اجمالي الساعات المعتمدة
                                                                        </h5>
                                                                        <p>
                                                                            120 ساعة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg fill="#2b2b2b" version="1.1" id="Capa_1"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                            width="64px" height="64px"
                                                                            viewBox="0 0 445.371 445.371"
                                                                            xml:space="preserve">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round"></g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <g>
                                                                                    <g>
                                                                                        <path
                                                                                            d="M83.199,388.629l0.136-0.312c2.311-5.006,6.526-8.812,12.534-11.33l19.052-8.773l1.01-0.85 c-0.726-0.834-1.435-1.691-2.121-2.578c-8.613-11.189-13.345-26.055-13.743-43.025l-2.639-1.205l-9.821-8.293l-15.781,15.702 h-0.011l-1.813-0.001h-0.011l-15.78-15.701l-9.823,8.293l-20.442,9.409c-3.025,1.245-5.997,3.128-7.51,6.401 c0,0-22.22,52.902-10.167,52.902h76.663C83.082,388.914,83.174,388.693,83.199,388.629z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M34.043,264.328c0.354,32.673,18.615,52.143,36.866,52.143c15.593,0,36.51-19.47,36.867-52.143 c0.226-22.666-10.57-36.238-36.867-36.238C44.611,228.089,33.813,241.662,34.043,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M197.729,376.986c6.184,2.588,10.563,6.609,12.675,11.664c0.023,0.051,0.11,0.266,0.261,0.617h24.042 c0.15-0.354,0.243-0.574,0.269-0.639l0.141-0.312c2.306-5.006,6.523-8.812,12.526-11.33l19.057-8.773l1.01-0.85 c-0.729-0.834-1.438-1.691-2.124-2.578c-8.612-11.189-13.345-26.055-13.737-43.025l-2.638-1.205l-9.821-8.293l-15.787,15.702 h-0.006l-1.818-0.001h-0.006l-15.785-15.701l-9.821,8.293l-2.642,1.207c-0.422,16.494-5.673,31.793-14.87,43.205 c-0.522,0.645-1.053,1.266-1.594,1.885l1.618,1.361L197.729,376.986z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M222.685,316.471c15.592,0,36.514-19.471,36.866-52.144c0.229-22.666-10.57-36.238-36.866-36.238 c-26.297,0-37.092,13.572-36.864,36.238C186.172,297,204.435,316.471,222.685,316.471z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M428.936,336.365c-1.318-3.139-4.485-5.156-7.51-6.401l-20.443-9.409l-9.822-8.293l-15.779,15.702h-0.013l-1.812-0.001 h-0.012l-15.779-15.701l-9.822,8.293l-2.641,1.207c-0.425,16.494-5.676,31.793-14.878,43.205c-0.52,0.645-1.052,1.266-1.592,1.885 l1.618,1.361l19.054,8.773c6.185,2.588,10.564,6.609,12.675,11.664c0.022,0.051,0.114,0.266,0.261,0.617h76.664 C451.155,389.268,428.936,336.365,428.936,336.365z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M337.596,264.328c0.354,32.673,18.616,52.143,36.866,52.143c15.593,0,36.512-19.47,36.865-52.143 c0.229-22.666-10.569-36.238-36.865-36.238C348.163,228.089,337.364,241.662,337.596,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M193.759,386.061l-20.44-9.416l-9.823-8.275l-15.78,15.691h-0.013h-1.813h-0.01l-15.781-15.691l-9.821,8.275 l-20.445,9.416c-3.024,1.236-5.997,3.117-7.508,6.406c0,0-22.221,52.904-10.168,52.904h129.282 c12.052,0-10.167-52.904-10.167-52.904C199.953,389.312,196.787,387.297,193.759,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M146.795,284.193c-26.297,0-37.093,13.562-36.865,36.23c0.356,32.663,18.618,52.15,36.865,52.15 c15.594,0,36.515-19.487,36.866-52.15C183.894,297.754,173.092,284.193,146.795,284.193z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M345.538,386.061l-20.445-9.416l-9.82-8.275l-15.782,15.691h-0.009h-1.815h-0.01l-15.779-15.691l-9.824,8.275 l-20.439,9.416c-3.026,1.236-5.996,3.117-7.514,6.406c0,0-22.218,52.904-10.166,52.904h129.282 c12.053,0-10.169-52.904-10.169-52.904C351.729,389.312,348.561,387.297,345.538,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M298.575,372.576c15.592,0,36.51-19.488,36.866-52.15c0.226-22.671-10.568-36.23-36.866-36.23 c-26.297,0-37.099,13.561-36.864,36.23C262.061,353.087,280.321,372.576,298.575,372.576z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M209.917,140.722c0.531,0.263,1.104,0.391,1.675,0.391c0.814,0,1.625-0.262,2.296-0.775l38.948-29.77 c0.937-0.715,1.486-1.826,1.486-3.004c0-1.179-0.55-2.289-1.486-3.006L213.888,74.79c-1.142-0.873-2.682-1.023-3.972-0.385 c-1.29,0.637-2.106,1.951-2.106,3.39v59.536C207.811,138.77,208.626,140.083,209.917,140.722z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M23.173,215.128h399.023c2.911,0,5.271-2.36,5.271-5.271V5.271c0-2.911-2.36-5.271-5.271-5.271H23.173 c-2.911,0-5.271,2.36-5.271,5.271v204.586C17.902,212.768,20.263,215.128,23.173,215.128z M222.686,46.254 c33.86,0,61.31,27.449,61.31,61.31c0,33.861-27.449,61.309-61.31,61.309c-33.86,0-61.31-27.448-61.31-61.309 C161.375,73.703,188.826,46.254,222.686,46.254z">
                                                                                        </path>
                                                                                    </g>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            المحاضرات
                                                                        </h5>
                                                                        <p>

                                                                            24 محاضرة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            </ul>

                                                        </div>

                                                        <div
                                                            class="price-div mb-3 d-flex justify-content-between w-100">
                                                            <div
                                                                class="d-flex align-items-center gap-2 justify-content-between">
                                                                <h3>
                                                                    رسوم البرنامج
                                                                </h3>
                                                            </div>
                                                            <h3 class="course-price">
                                                                13000 <svg viewBox="0 0 11 13" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="styles__icon--f9b3f"
                                                                    style="width: 20px; height: 20px; color: #fd9239;">
                                                                    <path
                                                                        d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z"
                                                                        fill="currentColor"></path>
                                                                </svg>
                                                            </h3>
                                                        </div>

                                                    </div>


                                                    <div class="d-flex gap-3 justify-content-between tooltip-btn">

                                                        <a href="{{ legacy_page('ar/request/%D8%A7%D9%84%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D8%AA%D9%82%D8%AF%D9%85%D8%A9-%D9%84%D8%B1%D9%8A%D8%A7%D8%AF%D8%A9-%D8%A7%D9%84%D8%A3%D8%B9%D9%85%D8%A7%D9%84.html') }}"
                                                            class="btn btn-primary ">

                                                            تقدم الان
                                                            <i class="feather-edit pe-2"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="fav-icon                 TrainingmakeWishlist                 "
                                                            data-training_id="141" title="إضافة إلى القائمة المفضلة">
                                                            <svg class="heart-icon " width="28px" height="28px"
                                                                viewBox="0 0 24 24" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">

                                                                <path class="heart-outline"
                                                                    d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z"
                                                                    fill="#8f8f8f"></path>

                                                                <path class="heart-filled"
                                                                    d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z"
                                                                    fill="var(--primary)"></path>
                                                            </svg> </a>

                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="owl-item active" style="width: 416px; margin-left: 24px;">
                                            <div class="gigs-grid">
                                                <div class="gigs-img">
                                                    <a
                                                        href="{{ legacy_page('ar/fellowship/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D9%88%D8%A7%D8%B1%D8%AF-%D8%A7%D9%84%D8%A8%D8%B4%D8%B1%D9%8A%D8%A9.html') }}">
                                                        <img src="{{ default_poster_url() }}" class="img-fluid"
                                                            alt=" ">
                                                    </a>
                                                    <div class="card-overlay-badge">
                                                    </div>
                                                    <a href="javascript:void(0)" class="finished soon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                            viewBox="0 0 16 16">
                                                            <path fill="currentColor" fill-rule="evenodd"
                                                                d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002ZM8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22V3.75ZM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0Zm-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0v-3.5Z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>

                                                        قريباً </a>
                                                </div>

                                                <div class="gigs-content">

                                                    <div class="gigs-info level-badge position-relative">
                                                        <span class="badge bg-primary-light">
                                                            الزمالة المهنية
                                                        </span>
                                                    </div>
                                                    <div class="gigs-title">
                                                        <h3 class="fw-bold">
                                                            <a href="{{ legacy_page('ar/fellowship/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D9%88%D8%A7%D8%B1%D8%AF-%D8%A7%D9%84%D8%A8%D8%B4%D8%B1%D9%8A%D8%A9.html') }}"
                                                                class="fw-bold">
                                                                زمالة الموارد البشرية
                                                            </a>
                                                        </h3>

                                                    </div>
                                                    <div class="card-overlay-badge">
                                                    </div>

                                                    <div class="">
                                                        <div class="card-content main-card">

                                                            <ul>

                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 10.4V20M12 10.4C12 8.15979 12 7.03969 11.564 6.18404C11.1805 5.43139 10.5686 4.81947 9.81596 4.43597C8.96031 4 7.84021 4 5.6 4H4.6C4.03995 4 3.75992 4 3.54601 4.10899C3.35785 4.20487 3.20487 4.35785 3.10899 4.54601C3 4.75992 3 5.03995 3 5.6V16.4C3 16.9601 3 17.2401 3.10899 17.454C3.20487 17.6422 3.35785 17.7951 3.54601 17.891C3.75992 18 4.03995 18 4.6 18H7.54668C8.08687 18 8.35696 18 8.61814 18.0466C8.84995 18.0879 9.0761 18.1563 9.29191 18.2506C9.53504 18.3567 9.75977 18.5065 10.2092 18.8062L12 20M12 10.4C12 8.15979 12 7.03969 12.436 6.18404C12.8195 5.43139 13.4314 4.81947 14.184 4.43597C15.0397 4 16.1598 4 18.4 4H19.4C19.9601 4 20.2401 4 20.454 4.10899C20.6422 4.20487 20.7951 4.35785 20.891 4.54601C21 4.75992 21 5.03995 21 5.6V16.4C21 16.9601 21 17.2401 20.891 17.454C20.7951 17.6422 20.6422 17.7951 20.454 17.891C20.2401 18 19.9601 18 19.4 18H16.4533C15.9131 18 15.643 18 15.3819 18.0466C15.15 18.0879 14.9239 18.1563 14.7081 18.2506C14.465 18.3567 14.2402 18.5065 13.7908 18.8062L12 20"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            آلية الدراسة
                                                                        </h5>
                                                                        <p>
                                                                            التعلم المدمج
                                                                        </p>
                                                                    </div>
                                                                </li>


                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            اجمالي الساعات المعتمدة
                                                                        </h5>
                                                                        <p>
                                                                            120 ساعة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg fill="#2b2b2b" version="1.1" id="Capa_1"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                            width="64px" height="64px"
                                                                            viewBox="0 0 445.371 445.371"
                                                                            xml:space="preserve">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round"></g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <g>
                                                                                    <g>
                                                                                        <path
                                                                                            d="M83.199,388.629l0.136-0.312c2.311-5.006,6.526-8.812,12.534-11.33l19.052-8.773l1.01-0.85 c-0.726-0.834-1.435-1.691-2.121-2.578c-8.613-11.189-13.345-26.055-13.743-43.025l-2.639-1.205l-9.821-8.293l-15.781,15.702 h-0.011l-1.813-0.001h-0.011l-15.78-15.701l-9.823,8.293l-20.442,9.409c-3.025,1.245-5.997,3.128-7.51,6.401 c0,0-22.22,52.902-10.167,52.902h76.663C83.082,388.914,83.174,388.693,83.199,388.629z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M34.043,264.328c0.354,32.673,18.615,52.143,36.866,52.143c15.593,0,36.51-19.47,36.867-52.143 c0.226-22.666-10.57-36.238-36.867-36.238C44.611,228.089,33.813,241.662,34.043,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M197.729,376.986c6.184,2.588,10.563,6.609,12.675,11.664c0.023,0.051,0.11,0.266,0.261,0.617h24.042 c0.15-0.354,0.243-0.574,0.269-0.639l0.141-0.312c2.306-5.006,6.523-8.812,12.526-11.33l19.057-8.773l1.01-0.85 c-0.729-0.834-1.438-1.691-2.124-2.578c-8.612-11.189-13.345-26.055-13.737-43.025l-2.638-1.205l-9.821-8.293l-15.787,15.702 h-0.006l-1.818-0.001h-0.006l-15.785-15.701l-9.821,8.293l-2.642,1.207c-0.422,16.494-5.673,31.793-14.87,43.205 c-0.522,0.645-1.053,1.266-1.594,1.885l1.618,1.361L197.729,376.986z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M222.685,316.471c15.592,0,36.514-19.471,36.866-52.144c0.229-22.666-10.57-36.238-36.866-36.238 c-26.297,0-37.092,13.572-36.864,36.238C186.172,297,204.435,316.471,222.685,316.471z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M428.936,336.365c-1.318-3.139-4.485-5.156-7.51-6.401l-20.443-9.409l-9.822-8.293l-15.779,15.702h-0.013l-1.812-0.001 h-0.012l-15.779-15.701l-9.822,8.293l-2.641,1.207c-0.425,16.494-5.676,31.793-14.878,43.205c-0.52,0.645-1.052,1.266-1.592,1.885 l1.618,1.361l19.054,8.773c6.185,2.588,10.564,6.609,12.675,11.664c0.022,0.051,0.114,0.266,0.261,0.617h76.664 C451.155,389.268,428.936,336.365,428.936,336.365z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M337.596,264.328c0.354,32.673,18.616,52.143,36.866,52.143c15.593,0,36.512-19.47,36.865-52.143 c0.229-22.666-10.569-36.238-36.865-36.238C348.163,228.089,337.364,241.662,337.596,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M193.759,386.061l-20.44-9.416l-9.823-8.275l-15.78,15.691h-0.013h-1.813h-0.01l-15.781-15.691l-9.821,8.275 l-20.445,9.416c-3.024,1.236-5.997,3.117-7.508,6.406c0,0-22.221,52.904-10.168,52.904h129.282 c12.052,0-10.167-52.904-10.167-52.904C199.953,389.312,196.787,387.297,193.759,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M146.795,284.193c-26.297,0-37.093,13.562-36.865,36.23c0.356,32.663,18.618,52.15,36.865,52.15 c15.594,0,36.515-19.487,36.866-52.15C183.894,297.754,173.092,284.193,146.795,284.193z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M345.538,386.061l-20.445-9.416l-9.82-8.275l-15.782,15.691h-0.009h-1.815h-0.01l-15.779-15.691l-9.824,8.275 l-20.439,9.416c-3.026,1.236-5.996,3.117-7.514,6.406c0,0-22.218,52.904-10.166,52.904h129.282 c12.053,0-10.169-52.904-10.169-52.904C351.729,389.312,348.561,387.297,345.538,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M298.575,372.576c15.592,0,36.51-19.488,36.866-52.15c0.226-22.671-10.568-36.23-36.866-36.23 c-26.297,0-37.099,13.561-36.864,36.23C262.061,353.087,280.321,372.576,298.575,372.576z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M209.917,140.722c0.531,0.263,1.104,0.391,1.675,0.391c0.814,0,1.625-0.262,2.296-0.775l38.948-29.77 c0.937-0.715,1.486-1.826,1.486-3.004c0-1.179-0.55-2.289-1.486-3.006L213.888,74.79c-1.142-0.873-2.682-1.023-3.972-0.385 c-1.29,0.637-2.106,1.951-2.106,3.39v59.536C207.811,138.77,208.626,140.083,209.917,140.722z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M23.173,215.128h399.023c2.911,0,5.271-2.36,5.271-5.271V5.271c0-2.911-2.36-5.271-5.271-5.271H23.173 c-2.911,0-5.271,2.36-5.271,5.271v204.586C17.902,212.768,20.263,215.128,23.173,215.128z M222.686,46.254 c33.86,0,61.31,27.449,61.31,61.31c0,33.861-27.449,61.309-61.31,61.309c-33.86,0-61.31-27.448-61.31-61.309 C161.375,73.703,188.826,46.254,222.686,46.254z">
                                                                                        </path>
                                                                                    </g>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            المحاضرات
                                                                        </h5>
                                                                        <p>

                                                                            24 محاضرة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            </ul>

                                                        </div>

                                                        <div
                                                            class="price-div mb-3 d-flex justify-content-between w-100">
                                                            <div
                                                                class="d-flex align-items-center gap-2 justify-content-between">
                                                                <h3>
                                                                    رسوم البرنامج
                                                                </h3>
                                                            </div>
                                                            <h3 class="course-price">
                                                                11000 <svg viewBox="0 0 11 13" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="styles__icon--f9b3f"
                                                                    style="width: 20px; height: 20px; color: #fd9239;">
                                                                    <path
                                                                        d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z"
                                                                        fill="currentColor"></path>
                                                                </svg>
                                                            </h3>
                                                        </div>

                                                    </div>


                                                    <div class="d-flex gap-3 justify-content-between tooltip-btn">

                                                        <a href="{{ legacy_page('ar/request/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D9%88%D8%A7%D8%B1%D8%AF-%D8%A7%D9%84%D8%A8%D8%B4%D8%B1%D9%8A%D8%A9.html') }}"
                                                            class="btn btn-primary ">

                                                            تقدم الان
                                                            <i class="feather-edit pe-2"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="fav-icon                 TrainingmakeWishlist                 "
                                                            data-training_id="142" title="إضافة إلى القائمة المفضلة">
                                                            <svg class="heart-icon " width="28px" height="28px"
                                                                viewBox="0 0 24 24" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">

                                                                <path class="heart-outline"
                                                                    d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z"
                                                                    fill="#8f8f8f"></path>

                                                                <path class="heart-filled"
                                                                    d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z"
                                                                    fill="var(--primary)"></path>
                                                            </svg> </a>

                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="owl-item active" style="width: 416px; margin-left: 24px;">
                                            <div class="gigs-grid">
                                                <div class="gigs-img">
                                                    <a
                                                        href="{{ legacy_page('ar/fellowship/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D8%AA%D8%B3%D9%88%D9%8A%D9%82-%D9%88%D8%A7%D9%84%D8%A7%D8%AA%D8%B5%D8%A7%D9%84-%D8%A7%D9%84%D9%85%D8%A4%D8%B3%D8%B3%D9%8A-%D9%88%D8%A7%D9%84%D9%85%D8%A8%D9%8A%D8%B9%D8%A7%D8%AA.html') }}">
                                                        <img src="{{ default_poster_url() }}" class="img-fluid"
                                                            alt=" ">
                                                    </a>
                                                    <div class="card-overlay-badge">
                                                    </div>
                                                    <a href="javascript:void(0)" class="finished soon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                            viewBox="0 0 16 16">
                                                            <path fill="currentColor" fill-rule="evenodd"
                                                                d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002ZM8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22V3.75ZM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0Zm-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0v-3.5Z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>

                                                        قريباً </a>
                                                </div>

                                                <div class="gigs-content">

                                                    <div class="gigs-info level-badge position-relative">
                                                        <span class="badge bg-primary-light">
                                                            الزمالة المهنية
                                                        </span>
                                                    </div>
                                                    <div class="gigs-title">
                                                        <h3 class="fw-bold">
                                                            <a href="{{ legacy_page('ar/fellowship/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D8%AA%D8%B3%D9%88%D9%8A%D9%82-%D9%88%D8%A7%D9%84%D8%A7%D8%AA%D8%B5%D8%A7%D9%84-%D8%A7%D9%84%D9%85%D8%A4%D8%B3%D8%B3%D9%8A-%D9%88%D8%A7%D9%84%D9%85%D8%A8%D9%8A%D8%B9%D8%A7%D8%AA.html') }}"
                                                                class="fw-bold">
                                                                زمالة التسويق والاتصال المؤسسي والمبيعات
                                                            </a>
                                                        </h3>

                                                    </div>
                                                    <div class="card-overlay-badge">
                                                    </div>

                                                    <div class="">
                                                        <div class="card-content main-card">

                                                            <ul>

                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 10.4V20M12 10.4C12 8.15979 12 7.03969 11.564 6.18404C11.1805 5.43139 10.5686 4.81947 9.81596 4.43597C8.96031 4 7.84021 4 5.6 4H4.6C4.03995 4 3.75992 4 3.54601 4.10899C3.35785 4.20487 3.20487 4.35785 3.10899 4.54601C3 4.75992 3 5.03995 3 5.6V16.4C3 16.9601 3 17.2401 3.10899 17.454C3.20487 17.6422 3.35785 17.7951 3.54601 17.891C3.75992 18 4.03995 18 4.6 18H7.54668C8.08687 18 8.35696 18 8.61814 18.0466C8.84995 18.0879 9.0761 18.1563 9.29191 18.2506C9.53504 18.3567 9.75977 18.5065 10.2092 18.8062L12 20M12 10.4C12 8.15979 12 7.03969 12.436 6.18404C12.8195 5.43139 13.4314 4.81947 14.184 4.43597C15.0397 4 16.1598 4 18.4 4H19.4C19.9601 4 20.2401 4 20.454 4.10899C20.6422 4.20487 20.7951 4.35785 20.891 4.54601C21 4.75992 21 5.03995 21 5.6V16.4C21 16.9601 21 17.2401 20.891 17.454C20.7951 17.6422 20.6422 17.7951 20.454 17.891C20.2401 18 19.9601 18 19.4 18H16.4533C15.9131 18 15.643 18 15.3819 18.0466C15.15 18.0879 14.9239 18.1563 14.7081 18.2506C14.465 18.3567 14.2402 18.5065 13.7908 18.8062L12 20"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            آلية الدراسة
                                                                        </h5>
                                                                        <p>
                                                                            التعلم المدمج
                                                                        </p>
                                                                    </div>
                                                                </li>


                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            اجمالي الساعات المعتمدة
                                                                        </h5>
                                                                        <p>
                                                                            120 ساعة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg fill="#2b2b2b" version="1.1" id="Capa_1"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                            width="64px" height="64px"
                                                                            viewBox="0 0 445.371 445.371"
                                                                            xml:space="preserve">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round"></g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <g>
                                                                                    <g>
                                                                                        <path
                                                                                            d="M83.199,388.629l0.136-0.312c2.311-5.006,6.526-8.812,12.534-11.33l19.052-8.773l1.01-0.85 c-0.726-0.834-1.435-1.691-2.121-2.578c-8.613-11.189-13.345-26.055-13.743-43.025l-2.639-1.205l-9.821-8.293l-15.781,15.702 h-0.011l-1.813-0.001h-0.011l-15.78-15.701l-9.823,8.293l-20.442,9.409c-3.025,1.245-5.997,3.128-7.51,6.401 c0,0-22.22,52.902-10.167,52.902h76.663C83.082,388.914,83.174,388.693,83.199,388.629z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M34.043,264.328c0.354,32.673,18.615,52.143,36.866,52.143c15.593,0,36.51-19.47,36.867-52.143 c0.226-22.666-10.57-36.238-36.867-36.238C44.611,228.089,33.813,241.662,34.043,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M197.729,376.986c6.184,2.588,10.563,6.609,12.675,11.664c0.023,0.051,0.11,0.266,0.261,0.617h24.042 c0.15-0.354,0.243-0.574,0.269-0.639l0.141-0.312c2.306-5.006,6.523-8.812,12.526-11.33l19.057-8.773l1.01-0.85 c-0.729-0.834-1.438-1.691-2.124-2.578c-8.612-11.189-13.345-26.055-13.737-43.025l-2.638-1.205l-9.821-8.293l-15.787,15.702 h-0.006l-1.818-0.001h-0.006l-15.785-15.701l-9.821,8.293l-2.642,1.207c-0.422,16.494-5.673,31.793-14.87,43.205 c-0.522,0.645-1.053,1.266-1.594,1.885l1.618,1.361L197.729,376.986z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M222.685,316.471c15.592,0,36.514-19.471,36.866-52.144c0.229-22.666-10.57-36.238-36.866-36.238 c-26.297,0-37.092,13.572-36.864,36.238C186.172,297,204.435,316.471,222.685,316.471z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M428.936,336.365c-1.318-3.139-4.485-5.156-7.51-6.401l-20.443-9.409l-9.822-8.293l-15.779,15.702h-0.013l-1.812-0.001 h-0.012l-15.779-15.701l-9.822,8.293l-2.641,1.207c-0.425,16.494-5.676,31.793-14.878,43.205c-0.52,0.645-1.052,1.266-1.592,1.885 l1.618,1.361l19.054,8.773c6.185,2.588,10.564,6.609,12.675,11.664c0.022,0.051,0.114,0.266,0.261,0.617h76.664 C451.155,389.268,428.936,336.365,428.936,336.365z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M337.596,264.328c0.354,32.673,18.616,52.143,36.866,52.143c15.593,0,36.512-19.47,36.865-52.143 c0.229-22.666-10.569-36.238-36.865-36.238C348.163,228.089,337.364,241.662,337.596,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M193.759,386.061l-20.44-9.416l-9.823-8.275l-15.78,15.691h-0.013h-1.813h-0.01l-15.781-15.691l-9.821,8.275 l-20.445,9.416c-3.024,1.236-5.997,3.117-7.508,6.406c0,0-22.221,52.904-10.168,52.904h129.282 c12.052,0-10.167-52.904-10.167-52.904C199.953,389.312,196.787,387.297,193.759,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M146.795,284.193c-26.297,0-37.093,13.562-36.865,36.23c0.356,32.663,18.618,52.15,36.865,52.15 c15.594,0,36.515-19.487,36.866-52.15C183.894,297.754,173.092,284.193,146.795,284.193z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M345.538,386.061l-20.445-9.416l-9.82-8.275l-15.782,15.691h-0.009h-1.815h-0.01l-15.779-15.691l-9.824,8.275 l-20.439,9.416c-3.026,1.236-5.996,3.117-7.514,6.406c0,0-22.218,52.904-10.166,52.904h129.282 c12.053,0-10.169-52.904-10.169-52.904C351.729,389.312,348.561,387.297,345.538,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M298.575,372.576c15.592,0,36.51-19.488,36.866-52.15c0.226-22.671-10.568-36.23-36.866-36.23 c-26.297,0-37.099,13.561-36.864,36.23C262.061,353.087,280.321,372.576,298.575,372.576z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M209.917,140.722c0.531,0.263,1.104,0.391,1.675,0.391c0.814,0,1.625-0.262,2.296-0.775l38.948-29.77 c0.937-0.715,1.486-1.826,1.486-3.004c0-1.179-0.55-2.289-1.486-3.006L213.888,74.79c-1.142-0.873-2.682-1.023-3.972-0.385 c-1.29,0.637-2.106,1.951-2.106,3.39v59.536C207.811,138.77,208.626,140.083,209.917,140.722z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M23.173,215.128h399.023c2.911,0,5.271-2.36,5.271-5.271V5.271c0-2.911-2.36-5.271-5.271-5.271H23.173 c-2.911,0-5.271,2.36-5.271,5.271v204.586C17.902,212.768,20.263,215.128,23.173,215.128z M222.686,46.254 c33.86,0,61.31,27.449,61.31,61.31c0,33.861-27.449,61.309-61.31,61.309c-33.86,0-61.31-27.448-61.31-61.309 C161.375,73.703,188.826,46.254,222.686,46.254z">
                                                                                        </path>
                                                                                    </g>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            المحاضرات
                                                                        </h5>
                                                                        <p>

                                                                            24 محاضرة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            </ul>

                                                        </div>

                                                        <div
                                                            class="price-div mb-3 d-flex justify-content-between w-100">
                                                            <div
                                                                class="d-flex align-items-center gap-2 justify-content-between">
                                                                <h3>
                                                                    رسوم البرنامج
                                                                </h3>
                                                            </div>
                                                            <h3 class="course-price">
                                                                12000 <svg viewBox="0 0 11 13" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="styles__icon--f9b3f"
                                                                    style="width: 20px; height: 20px; color: #fd9239;">
                                                                    <path
                                                                        d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z"
                                                                        fill="currentColor"></path>
                                                                </svg>
                                                            </h3>
                                                        </div>

                                                    </div>


                                                    <div class="d-flex gap-3 justify-content-between tooltip-btn">

                                                        <a href="{{ legacy_page('ar/request/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D8%AA%D8%B3%D9%88%D9%8A%D9%82-%D9%88%D8%A7%D9%84%D8%A7%D8%AA%D8%B5%D8%A7%D9%84-%D8%A7%D9%84%D9%85%D8%A4%D8%B3%D8%B3%D9%8A-%D9%88%D8%A7%D9%84%D9%85%D8%A8%D9%8A%D8%B9%D8%A7%D8%AA.html') }}"
                                                            class="btn btn-primary ">

                                                            تقدم الان
                                                            <i class="feather-edit pe-2"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="fav-icon                 TrainingmakeWishlist                 "
                                                            data-training_id="143" title="إضافة إلى القائمة المفضلة">
                                                            <svg class="heart-icon " width="28px" height="28px"
                                                                viewBox="0 0 24 24" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">

                                                                <path class="heart-outline"
                                                                    d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z"
                                                                    fill="#8f8f8f"></path>

                                                                <path class="heart-filled"
                                                                    d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z"
                                                                    fill="var(--primary)"></path>
                                                            </svg> </a>

                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="owl-item" style="width: 416px; margin-left: 24px;">
                                            <div class="gigs-grid">
                                                <div class="gigs-img">
                                                    <a
                                                        href="{{ legacy_page('ar/fellowship/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D8%AA%D8%AD%D8%AF%D8%AB-%D8%A7%D9%84%D8%B1%D8%B3%D9%85%D9%8A-%D9%88%D8%A7%D9%84%D8%A5%D8%B9%D9%84%D8%A7%D9%85-%D8%A7%D9%84%D9%85%D8%A4%D8%B3%D8%B3%D9%8A.html') }}">
                                                        <img src="{{ default_poster_url() }}" class="img-fluid"
                                                            alt=" ">
                                                    </a>
                                                    <div class="card-overlay-badge">
                                                    </div>
                                                    <a href="javascript:void(0)" class="finished soon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                            viewBox="0 0 16 16">
                                                            <path fill="currentColor" fill-rule="evenodd"
                                                                d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002ZM8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22V3.75ZM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0Zm-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0v-3.5Z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>

                                                        قريباً </a>
                                                </div>

                                                <div class="gigs-content">

                                                    <div class="gigs-info level-badge position-relative">
                                                        <span class="badge bg-primary-light">
                                                            الزمالة المهنية
                                                        </span>
                                                    </div>
                                                    <div class="gigs-title">
                                                        <h3 class="fw-bold">
                                                            <a href="{{ legacy_page('ar/fellowship/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D8%AA%D8%AD%D8%AF%D8%AB-%D8%A7%D9%84%D8%B1%D8%B3%D9%85%D9%8A-%D9%88%D8%A7%D9%84%D8%A5%D8%B9%D9%84%D8%A7%D9%85-%D8%A7%D9%84%D9%85%D8%A4%D8%B3%D8%B3%D9%8A.html') }}"
                                                                class="fw-bold">
                                                                زمالة المتحدث الرسمي والإعلام المؤسسي
                                                            </a>
                                                        </h3>

                                                    </div>
                                                    <div class="card-overlay-badge">
                                                    </div>

                                                    <div class="">
                                                        <div class="card-content main-card">

                                                            <ul>

                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 10.4V20M12 10.4C12 8.15979 12 7.03969 11.564 6.18404C11.1805 5.43139 10.5686 4.81947 9.81596 4.43597C8.96031 4 7.84021 4 5.6 4H4.6C4.03995 4 3.75992 4 3.54601 4.10899C3.35785 4.20487 3.20487 4.35785 3.10899 4.54601C3 4.75992 3 5.03995 3 5.6V16.4C3 16.9601 3 17.2401 3.10899 17.454C3.20487 17.6422 3.35785 17.7951 3.54601 17.891C3.75992 18 4.03995 18 4.6 18H7.54668C8.08687 18 8.35696 18 8.61814 18.0466C8.84995 18.0879 9.0761 18.1563 9.29191 18.2506C9.53504 18.3567 9.75977 18.5065 10.2092 18.8062L12 20M12 10.4C12 8.15979 12 7.03969 12.436 6.18404C12.8195 5.43139 13.4314 4.81947 14.184 4.43597C15.0397 4 16.1598 4 18.4 4H19.4C19.9601 4 20.2401 4 20.454 4.10899C20.6422 4.20487 20.7951 4.35785 20.891 4.54601C21 4.75992 21 5.03995 21 5.6V16.4C21 16.9601 21 17.2401 20.891 17.454C20.7951 17.6422 20.6422 17.7951 20.454 17.891C20.2401 18 19.9601 18 19.4 18H16.4533C15.9131 18 15.643 18 15.3819 18.0466C15.15 18.0879 14.9239 18.1563 14.7081 18.2506C14.465 18.3567 14.2402 18.5065 13.7908 18.8062L12 20"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            آلية الدراسة
                                                                        </h5>
                                                                        <p>
                                                                            التعلم المدمج
                                                                        </p>
                                                                    </div>
                                                                </li>


                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            اجمالي الساعات المعتمدة
                                                                        </h5>
                                                                        <p>
                                                                            120 ساعة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg fill="#2b2b2b" version="1.1" id="Capa_1"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                            width="64px" height="64px"
                                                                            viewBox="0 0 445.371 445.371"
                                                                            xml:space="preserve">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round"></g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <g>
                                                                                    <g>
                                                                                        <path
                                                                                            d="M83.199,388.629l0.136-0.312c2.311-5.006,6.526-8.812,12.534-11.33l19.052-8.773l1.01-0.85 c-0.726-0.834-1.435-1.691-2.121-2.578c-8.613-11.189-13.345-26.055-13.743-43.025l-2.639-1.205l-9.821-8.293l-15.781,15.702 h-0.011l-1.813-0.001h-0.011l-15.78-15.701l-9.823,8.293l-20.442,9.409c-3.025,1.245-5.997,3.128-7.51,6.401 c0,0-22.22,52.902-10.167,52.902h76.663C83.082,388.914,83.174,388.693,83.199,388.629z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M34.043,264.328c0.354,32.673,18.615,52.143,36.866,52.143c15.593,0,36.51-19.47,36.867-52.143 c0.226-22.666-10.57-36.238-36.867-36.238C44.611,228.089,33.813,241.662,34.043,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M197.729,376.986c6.184,2.588,10.563,6.609,12.675,11.664c0.023,0.051,0.11,0.266,0.261,0.617h24.042 c0.15-0.354,0.243-0.574,0.269-0.639l0.141-0.312c2.306-5.006,6.523-8.812,12.526-11.33l19.057-8.773l1.01-0.85 c-0.729-0.834-1.438-1.691-2.124-2.578c-8.612-11.189-13.345-26.055-13.737-43.025l-2.638-1.205l-9.821-8.293l-15.787,15.702 h-0.006l-1.818-0.001h-0.006l-15.785-15.701l-9.821,8.293l-2.642,1.207c-0.422,16.494-5.673,31.793-14.87,43.205 c-0.522,0.645-1.053,1.266-1.594,1.885l1.618,1.361L197.729,376.986z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M222.685,316.471c15.592,0,36.514-19.471,36.866-52.144c0.229-22.666-10.57-36.238-36.866-36.238 c-26.297,0-37.092,13.572-36.864,36.238C186.172,297,204.435,316.471,222.685,316.471z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M428.936,336.365c-1.318-3.139-4.485-5.156-7.51-6.401l-20.443-9.409l-9.822-8.293l-15.779,15.702h-0.013l-1.812-0.001 h-0.012l-15.779-15.701l-9.822,8.293l-2.641,1.207c-0.425,16.494-5.676,31.793-14.878,43.205c-0.52,0.645-1.052,1.266-1.592,1.885 l1.618,1.361l19.054,8.773c6.185,2.588,10.564,6.609,12.675,11.664c0.022,0.051,0.114,0.266,0.261,0.617h76.664 C451.155,389.268,428.936,336.365,428.936,336.365z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M337.596,264.328c0.354,32.673,18.616,52.143,36.866,52.143c15.593,0,36.512-19.47,36.865-52.143 c0.229-22.666-10.569-36.238-36.865-36.238C348.163,228.089,337.364,241.662,337.596,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M193.759,386.061l-20.44-9.416l-9.823-8.275l-15.78,15.691h-0.013h-1.813h-0.01l-15.781-15.691l-9.821,8.275 l-20.445,9.416c-3.024,1.236-5.997,3.117-7.508,6.406c0,0-22.221,52.904-10.168,52.904h129.282 c12.052,0-10.167-52.904-10.167-52.904C199.953,389.312,196.787,387.297,193.759,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M146.795,284.193c-26.297,0-37.093,13.562-36.865,36.23c0.356,32.663,18.618,52.15,36.865,52.15 c15.594,0,36.515-19.487,36.866-52.15C183.894,297.754,173.092,284.193,146.795,284.193z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M345.538,386.061l-20.445-9.416l-9.82-8.275l-15.782,15.691h-0.009h-1.815h-0.01l-15.779-15.691l-9.824,8.275 l-20.439,9.416c-3.026,1.236-5.996,3.117-7.514,6.406c0,0-22.218,52.904-10.166,52.904h129.282 c12.053,0-10.169-52.904-10.169-52.904C351.729,389.312,348.561,387.297,345.538,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M298.575,372.576c15.592,0,36.51-19.488,36.866-52.15c0.226-22.671-10.568-36.23-36.866-36.23 c-26.297,0-37.099,13.561-36.864,36.23C262.061,353.087,280.321,372.576,298.575,372.576z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M209.917,140.722c0.531,0.263,1.104,0.391,1.675,0.391c0.814,0,1.625-0.262,2.296-0.775l38.948-29.77 c0.937-0.715,1.486-1.826,1.486-3.004c0-1.179-0.55-2.289-1.486-3.006L213.888,74.79c-1.142-0.873-2.682-1.023-3.972-0.385 c-1.29,0.637-2.106,1.951-2.106,3.39v59.536C207.811,138.77,208.626,140.083,209.917,140.722z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M23.173,215.128h399.023c2.911,0,5.271-2.36,5.271-5.271V5.271c0-2.911-2.36-5.271-5.271-5.271H23.173 c-2.911,0-5.271,2.36-5.271,5.271v204.586C17.902,212.768,20.263,215.128,23.173,215.128z M222.686,46.254 c33.86,0,61.31,27.449,61.31,61.31c0,33.861-27.449,61.309-61.31,61.309c-33.86,0-61.31-27.448-61.31-61.309 C161.375,73.703,188.826,46.254,222.686,46.254z">
                                                                                        </path>
                                                                                    </g>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            المحاضرات
                                                                        </h5>
                                                                        <p>

                                                                            24 محاضرة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            </ul>

                                                        </div>

                                                        <div
                                                            class="price-div mb-3 d-flex justify-content-between w-100">
                                                            <div
                                                                class="d-flex align-items-center gap-2 justify-content-between">
                                                                <h3>
                                                                    رسوم البرنامج
                                                                </h3>
                                                            </div>
                                                            <h3 class="course-price">
                                                                14000 <svg viewBox="0 0 11 13" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="styles__icon--f9b3f"
                                                                    style="width: 20px; height: 20px; color: #fd9239;">
                                                                    <path
                                                                        d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z"
                                                                        fill="currentColor"></path>
                                                                </svg>
                                                            </h3>
                                                        </div>

                                                    </div>


                                                    <div class="d-flex gap-3 justify-content-between tooltip-btn">

                                                        <a href="{{ legacy_page('ar/request/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D9%85%D8%AA%D8%AD%D8%AF%D8%AB-%D8%A7%D9%84%D8%B1%D8%B3%D9%85%D9%8A-%D9%88%D8%A7%D9%84%D8%A5%D8%B9%D9%84%D8%A7%D9%85-%D8%A7%D9%84%D9%85%D8%A4%D8%B3%D8%B3%D9%8A.html') }}"
                                                            class="btn btn-primary ">

                                                            تقدم الان
                                                            <i class="feather-edit pe-2"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="fav-icon                 TrainingmakeWishlist                 "
                                                            data-training_id="144" title="إضافة إلى القائمة المفضلة">
                                                            <svg class="heart-icon " width="28px" height="28px"
                                                                viewBox="0 0 24 24" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">

                                                                <path class="heart-outline"
                                                                    d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z"
                                                                    fill="#8f8f8f"></path>

                                                                <path class="heart-filled"
                                                                    d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z"
                                                                    fill="var(--primary)"></path>
                                                            </svg> </a>

                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="owl-item" style="width: 416px; margin-left: 24px;">
                                            <div class="gigs-grid">
                                                <div class="gigs-img">
                                                    <a
                                                        href="{{ legacy_page('ar/fellowship/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D8%B0%D9%83%D8%A7%D8%A1-%D8%A7%D9%84%D8%A7%D8%B5%D8%B7%D9%86%D8%A7%D8%B9%D9%8A-%D9%84%D9%84%D8%A3%D8%B9%D9%85%D8%A7%D9%84.html') }}">
                                                        <img src="{{ default_poster_url() }}" class="img-fluid"
                                                            alt=" ">
                                                    </a>
                                                    <div class="card-overlay-badge">
                                                    </div>
                                                    <a href="javascript:void(0)" class="finished soon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                            viewBox="0 0 16 16">
                                                            <path fill="currentColor" fill-rule="evenodd"
                                                                d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002ZM8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22V3.75ZM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0Zm-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0v-3.5Z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>

                                                        قريباً </a>
                                                </div>

                                                <div class="gigs-content">

                                                    <div class="gigs-info level-badge position-relative">
                                                        <span class="badge bg-primary-light">
                                                            الزمالة المهنية
                                                        </span>
                                                    </div>
                                                    <div class="gigs-title">
                                                        <h3 class="fw-bold">
                                                            <a href="{{ legacy_page('ar/fellowship/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D8%B0%D9%83%D8%A7%D8%A1-%D8%A7%D9%84%D8%A7%D8%B5%D8%B7%D9%86%D8%A7%D8%B9%D9%8A-%D9%84%D9%84%D8%A3%D8%B9%D9%85%D8%A7%D9%84.html') }}"
                                                                class="fw-bold">
                                                                زمالة الذكاء الاصطناعي للأعمال
                                                            </a>
                                                        </h3>

                                                    </div>
                                                    <div class="card-overlay-badge">
                                                    </div>

                                                    <div class="">
                                                        <div class="card-content main-card">

                                                            <ul>

                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 10.4V20M12 10.4C12 8.15979 12 7.03969 11.564 6.18404C11.1805 5.43139 10.5686 4.81947 9.81596 4.43597C8.96031 4 7.84021 4 5.6 4H4.6C4.03995 4 3.75992 4 3.54601 4.10899C3.35785 4.20487 3.20487 4.35785 3.10899 4.54601C3 4.75992 3 5.03995 3 5.6V16.4C3 16.9601 3 17.2401 3.10899 17.454C3.20487 17.6422 3.35785 17.7951 3.54601 17.891C3.75992 18 4.03995 18 4.6 18H7.54668C8.08687 18 8.35696 18 8.61814 18.0466C8.84995 18.0879 9.0761 18.1563 9.29191 18.2506C9.53504 18.3567 9.75977 18.5065 10.2092 18.8062L12 20M12 10.4C12 8.15979 12 7.03969 12.436 6.18404C12.8195 5.43139 13.4314 4.81947 14.184 4.43597C15.0397 4 16.1598 4 18.4 4H19.4C19.9601 4 20.2401 4 20.454 4.10899C20.6422 4.20487 20.7951 4.35785 20.891 4.54601C21 4.75992 21 5.03995 21 5.6V16.4C21 16.9601 21 17.2401 20.891 17.454C20.7951 17.6422 20.6422 17.7951 20.454 17.891C20.2401 18 19.9601 18 19.4 18H16.4533C15.9131 18 15.643 18 15.3819 18.0466C15.15 18.0879 14.9239 18.1563 14.7081 18.2506C14.465 18.3567 14.2402 18.5065 13.7908 18.8062L12 20"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            آلية الدراسة
                                                                        </h5>
                                                                        <p>
                                                                            التعلم المدمج
                                                                        </p>
                                                                    </div>
                                                                </li>


                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg width="64px" height="64px"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round">
                                                                            </g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <path
                                                                                    d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                                                                    stroke="#2b2b2b" stroke-width="2"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"></path>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            اجمالي الساعات المعتمدة
                                                                        </h5>
                                                                        <p>
                                                                            120 ساعة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <div class="sidbar-icon">
                                                                        <svg fill="#2b2b2b" version="1.1" id="Capa_1"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                            width="64px" height="64px"
                                                                            viewBox="0 0 445.371 445.371"
                                                                            xml:space="preserve">
                                                                            <g id="SVGRepo_bgCarrier" stroke-width="0">
                                                                            </g>
                                                                            <g id="SVGRepo_tracerCarrier"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round"></g>
                                                                            <g id="SVGRepo_iconCarrier">
                                                                                <g>
                                                                                    <g>
                                                                                        <path
                                                                                            d="M83.199,388.629l0.136-0.312c2.311-5.006,6.526-8.812,12.534-11.33l19.052-8.773l1.01-0.85 c-0.726-0.834-1.435-1.691-2.121-2.578c-8.613-11.189-13.345-26.055-13.743-43.025l-2.639-1.205l-9.821-8.293l-15.781,15.702 h-0.011l-1.813-0.001h-0.011l-15.78-15.701l-9.823,8.293l-20.442,9.409c-3.025,1.245-5.997,3.128-7.51,6.401 c0,0-22.22,52.902-10.167,52.902h76.663C83.082,388.914,83.174,388.693,83.199,388.629z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M34.043,264.328c0.354,32.673,18.615,52.143,36.866,52.143c15.593,0,36.51-19.47,36.867-52.143 c0.226-22.666-10.57-36.238-36.867-36.238C44.611,228.089,33.813,241.662,34.043,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M197.729,376.986c6.184,2.588,10.563,6.609,12.675,11.664c0.023,0.051,0.11,0.266,0.261,0.617h24.042 c0.15-0.354,0.243-0.574,0.269-0.639l0.141-0.312c2.306-5.006,6.523-8.812,12.526-11.33l19.057-8.773l1.01-0.85 c-0.729-0.834-1.438-1.691-2.124-2.578c-8.612-11.189-13.345-26.055-13.737-43.025l-2.638-1.205l-9.821-8.293l-15.787,15.702 h-0.006l-1.818-0.001h-0.006l-15.785-15.701l-9.821,8.293l-2.642,1.207c-0.422,16.494-5.673,31.793-14.87,43.205 c-0.522,0.645-1.053,1.266-1.594,1.885l1.618,1.361L197.729,376.986z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M222.685,316.471c15.592,0,36.514-19.471,36.866-52.144c0.229-22.666-10.57-36.238-36.866-36.238 c-26.297,0-37.092,13.572-36.864,36.238C186.172,297,204.435,316.471,222.685,316.471z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M428.936,336.365c-1.318-3.139-4.485-5.156-7.51-6.401l-20.443-9.409l-9.822-8.293l-15.779,15.702h-0.013l-1.812-0.001 h-0.012l-15.779-15.701l-9.822,8.293l-2.641,1.207c-0.425,16.494-5.676,31.793-14.878,43.205c-0.52,0.645-1.052,1.266-1.592,1.885 l1.618,1.361l19.054,8.773c6.185,2.588,10.564,6.609,12.675,11.664c0.022,0.051,0.114,0.266,0.261,0.617h76.664 C451.155,389.268,428.936,336.365,428.936,336.365z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M337.596,264.328c0.354,32.673,18.616,52.143,36.866,52.143c15.593,0,36.512-19.47,36.865-52.143 c0.229-22.666-10.569-36.238-36.865-36.238C348.163,228.089,337.364,241.662,337.596,264.328z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M193.759,386.061l-20.44-9.416l-9.823-8.275l-15.78,15.691h-0.013h-1.813h-0.01l-15.781-15.691l-9.821,8.275 l-20.445,9.416c-3.024,1.236-5.997,3.117-7.508,6.406c0,0-22.221,52.904-10.168,52.904h129.282 c12.052,0-10.167-52.904-10.167-52.904C199.953,389.312,196.787,387.297,193.759,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M146.795,284.193c-26.297,0-37.093,13.562-36.865,36.23c0.356,32.663,18.618,52.15,36.865,52.15 c15.594,0,36.515-19.487,36.866-52.15C183.894,297.754,173.092,284.193,146.795,284.193z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M345.538,386.061l-20.445-9.416l-9.82-8.275l-15.782,15.691h-0.009h-1.815h-0.01l-15.779-15.691l-9.824,8.275 l-20.439,9.416c-3.026,1.236-5.996,3.117-7.514,6.406c0,0-22.218,52.904-10.166,52.904h129.282 c12.053,0-10.169-52.904-10.169-52.904C351.729,389.312,348.561,387.297,345.538,386.061z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M298.575,372.576c15.592,0,36.51-19.488,36.866-52.15c0.226-22.671-10.568-36.23-36.866-36.23 c-26.297,0-37.099,13.561-36.864,36.23C262.061,353.087,280.321,372.576,298.575,372.576z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M209.917,140.722c0.531,0.263,1.104,0.391,1.675,0.391c0.814,0,1.625-0.262,2.296-0.775l38.948-29.77 c0.937-0.715,1.486-1.826,1.486-3.004c0-1.179-0.55-2.289-1.486-3.006L213.888,74.79c-1.142-0.873-2.682-1.023-3.972-0.385 c-1.29,0.637-2.106,1.951-2.106,3.39v59.536C207.811,138.77,208.626,140.083,209.917,140.722z">
                                                                                        </path>
                                                                                        <path
                                                                                            d="M23.173,215.128h399.023c2.911,0,5.271-2.36,5.271-5.271V5.271c0-2.911-2.36-5.271-5.271-5.271H23.173 c-2.911,0-5.271,2.36-5.271,5.271v204.586C17.902,212.768,20.263,215.128,23.173,215.128z M222.686,46.254 c33.86,0,61.31,27.449,61.31,61.31c0,33.861-27.449,61.309-61.31,61.309c-33.86,0-61.31-27.448-61.31-61.309 C161.375,73.703,188.826,46.254,222.686,46.254z">
                                                                                        </path>
                                                                                    </g>
                                                                                </g>
                                                                            </g>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between w-100">
                                                                        <h5>
                                                                            المحاضرات
                                                                        </h5>
                                                                        <p>

                                                                            24 محاضرة
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            </ul>

                                                        </div>

                                                        <div
                                                            class="price-div mb-3 d-flex justify-content-between w-100">
                                                            <div
                                                                class="d-flex align-items-center gap-2 justify-content-between">
                                                                <h3>
                                                                    رسوم البرنامج
                                                                </h3>
                                                            </div>
                                                            <h3 class="course-price">
                                                                14000 <svg viewBox="0 0 11 13" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="styles__icon--f9b3f"
                                                                    style="width: 20px; height: 20px; color: #fd9239;">
                                                                    <path
                                                                        d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z"
                                                                        fill="currentColor"></path>
                                                                </svg>
                                                            </h3>
                                                        </div>

                                                    </div>


                                                    <div class="d-flex gap-3 justify-content-between tooltip-btn">

                                                        <a href="{{ legacy_page('ar/request/%D8%B2%D9%85%D8%A7%D9%84%D8%A9-%D8%A7%D9%84%D8%B0%D9%83%D8%A7%D8%A1-%D8%A7%D9%84%D8%A7%D8%B5%D8%B7%D9%86%D8%A7%D8%B9%D9%8A-%D9%84%D9%84%D8%A3%D8%B9%D9%85%D8%A7%D9%84.html') }}"
                                                            class="btn btn-primary ">

                                                            تقدم الان
                                                            <i class="feather-edit pe-2"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="fav-icon                 TrainingmakeWishlist                 "
                                                            data-training_id="145" title="إضافة إلى القائمة المفضلة">
                                                            <svg class="heart-icon " width="28px" height="28px"
                                                                viewBox="0 0 24 24" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">

                                                                <path class="heart-outline"
                                                                    d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.601 6.16763 11.7961 6.25063 12 6.25063C12.2039 6.25063 12.399 6.16763 12.5404 6.02073L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219Z"
                                                                    fill="#8f8f8f"></path>

                                                                <path class="heart-filled"
                                                                    d="M12 20.25C11.3096 20.25 10.6739 19.9854 10.1168 19.6599C9.55954 19.3343 9.00965 18.9037 8.49742 18.4999C7.07077 17.3752 5.24723 16.0866 3.81672 14.4758C2.3605 12.8361 1.25 10.8026 1.25 8.1371C1.25 5.42503 2.78471 3.07292 5.00076 2.05996C7.26409 1.02539 10.0985 1.44352 12.5404 3.98053C14.9015 1.44352 17.7359 1.02539 19.9992 2.05996C21.2153 3.07292 22.75 5.42503 22.75 8.1371C22.75 10.8026 21.6395 12.8361 20.1833 14.4758C18.7528 16.0866 16.9292 17.3752 15.5026 18.4999C14.9903 18.9037 14.4405 19.3343 13.8832 19.6599C13.3261 19.9854 12.6904 20.25 12 20.25Z"
                                                                    fill="var(--primary)"></path>
                                                            </svg> </a>

                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="owl-dots disabled"></div>
                            </div>
                        </div>
                        <div class="w-100 text-center mt-4">
                            <a href="{{ legacy_page('ar/fellowships.html') }}" class="btn btn-primary">
                                جميع الزمالات المهنية </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Explore Gigs -->


        <!-- Find Your Needs -->
        <section class="provide-section">
            <div class="container">
                <div class="row d-none">
                    <div class="col-lg-6 col-md-9">
                        <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                            <h2>نحن هنا لنبسط لك الاشتراك بالبرامج الدراسية</h2>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="provide-box">
                            <div class="provide-icon">
                                <img src="{{ static_asset('assets/1853033571057247.png') }}" alt="أيقونة">
                            </div>
                            <h3>مهمتنا</h3>
                            <p></p>
                            <p dir="rtl" data-start="283" data-end="430">تقديم برامج تعليمية وتدريبية مبتكرة ومرنة مبنية
                                على احتياجات سوق العمل، بالشراكة مع الخبراء والجهات الأكاديمية، بهدف تأهيل كوادر وطنية
                                قادرة على المنافسة عالميًا وتحقيق التنمية المستدامة.</p>
                            <p></p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="provide-box">
                            <div class="provide-icon">
                                <img src="{{ static_asset('assets/1853033393294593.png') }}" alt="أيقونة">
                            </div>
                            <h3>رؤيتنا</h3>
                            <p></p>
                            <p dir="rtl" data-start="139" data-end="281">أن تكون منصة مركز التعلم المستمر المرجع الرائد إقليميًا في
                                تقديم التعليم الاحترافي، وبوابة التحول المعرفي التي تمكّن الأفراد والمؤسسات من اكتساب
                                مهارات المستقبل وفق أعلى المعايير الأكاديمية والمهنية.</p>
                            <p dir="rtl" data-start="283" data-end="430">&nbsp;</p>
                            <p></p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="provide-box">
                            <div class="provide-icon">
                                <img src="{{ static_asset('assets/1853033717546615.png') }}" alt="أيقونة">
                            </div>
                            <h3>أهدافنا</h3>
                            <p></p>
                            <div class="flex flex-col text-sm pb-25">
                                <article
                                    class="text-token-text-primary w-full focus:outline-none [--shadow-height:45px] has-data-writing-block:pointer-events-none has-data-writing-block:-mt-(--shadow-height) has-data-writing-block:pt-(--shadow-height) [&amp;:has([data-writing-block])&gt;*]:pointer-events-auto scroll-mt-[calc(var(--header-height)+min(200px,max(70px,20svh)))]"
                                    dir="auto" tabindex="-1"
                                    data-turn-id="request-WEB:16e42f65-c950-4733-80b7-a67e9792a55a-3"
                                    data-testid="conversation-turn-8" data-scroll-anchor="true" data-turn="assistant">
                                    <div
                                        class="text-base my-auto mx-auto pb-10 [--thread-content-margin:--spacing(4)] @w-sm/main:[--thread-content-margin:--spacing(6)] @w-lg/main:[--thread-content-margin:--spacing(16)] px-(--thread-content-margin)">
                                        <div class="[--thread-content-max-width:40rem] @w-lg/main:[--thread-content-max-width:48rem] mx-auto max-w-(--thread-content-max-width) flex-1 group/turn-messages focus-visible:outline-hidden relative flex w-full min-w-0 flex-col agent-turn"
                                            tabindex="-1">
                                            <div class="flex max-w-full flex-col grow">
                                                <div class="min-h-8 text-message relative flex w-full flex-col items-end gap-2 text-start break-words whitespace-normal [.text-message+&amp;]:mt-1"
                                                    dir="auto" data-message-author-role="assistant"
                                                    data-message-id="386dd5bb-6432-40fb-8fd5-c1ebf1df3aca"
                                                    data-message-model-slug="gpt-5-2">
                                                    <div class="flex w-full flex-col gap-1 empty:hidden first:pt-[1px]">
                                                        <div
                                                            class="markdown prose dark:prose-invert w-full wrap-break-word light markdown-new-styling">
                                                            <p data-start="0" data-end="161" data-is-last-node=""
                                                                data-is-only-node="">تهدف منصة مركز التعلم المستمر إلى تقديم برامج
                                                                تعليمية احترافية مواكبة لسوق العمل تنمّي المهارات
                                                                التطبيقية وتدعم التطور المهني، من خلال محتوى عالي الجودة
                                                                وتقنيات تعليم حديثة</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3 w-full empty:hidden">
                                                <div class="text-center">&nbsp;</div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            <div class="pointer-events-none h-px w-px absolute bottom-0" aria-hidden="true"
                                data-edge="true">&nbsp;</div>
                            <p></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Find Your Needs -->


        <!-- Explore Gigs -->
        <section id="section-mahara" class="explore-gigs-section">
            <div class="container">
                <div class="section-head d-flex">
                    <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                        <h2> برنامج مهارات</h2>
                    </div>
                </div>
                <div class="blog">
                    <div class="row">
                        <!-- ONLY CHANGE: columns updated to 3 per row -->
                        <div class="col-12 col-md-6">
                            <div class="blog-grid program">
                                <div class="blog-img">
                                    <a
                                        href="{{ legacy_page('ar/service/%D9%85%D9%87%D8%A7%D8%B1%D8%A7%D8%AA-%D8%B9%D8%A7%D9%85%D8%A9-%D8%A8%D8%A7%D8%AD%D8%AB%D9%8A%D9%86-%D8%B9%D9%86-%D8%B9%D9%85%D9%84.html') }}">
                                        <img src="{{ default_poster_url() }}" class="img-fluid" alt="img"></a>
                                </div>

                                <!--<div class="blog-content">-->
                                <!--    <div class="blog-title">-->
                                <!--        <h3>-->
                                <!--            <a href="{{ legacy_page('ar/service/مهارات-عامة-باحثين-عن-عمل.html') }}">-->
                                <!--                مهارات عامة - باحثين عن عمل-->
                                <!--            </a>-->
                                <!--        </h3>-->
                                <!--    </div>-->

                                <!--<div class="blog-content-footer d-flex justify-content-between align-items-center">-->
                                <!--    <p>-->
                                <!--        <span><i class="feather-clock"></i></span>منذ 4 أشهر-->
                                <!--    </p>-->
                                <!--</div>-->

                                <!--    -->

                                <!--    <div class="gigs-card-footer justify-content-start gap-2">-->
                                <!--        <a class="btn btn-primary"-->
                                <!--           href="{{ legacy_page('ar/service/مهارات-عامة-باحثين-عن-عمل.html') }}">-->
                                <!--            مزيد من التفاصيل-->
                                <!--            <i class="feather-eye pe-2"></i>-->
                                <!--        </a>-->
                                <!--    </div>-->
                                <!--</div>-->
                            </div>
                        </div>
                        <!-- ONLY CHANGE: columns updated to 3 per row -->
                        <div class="col-12 col-md-6">
                            <div class="blog-grid program">
                                <div class="blog-img">
                                    <a
                                        href="{{ legacy_page('ar/service/%D9%85%D9%87%D8%A7%D8%B1%D8%A7%D8%AA-%D8%B9%D8%A7%D9%85%D8%A9-%D9%85%D9%88%D8%B8%D9%81%D9%8A%D9%86-%D8%B9%D9%84%D9%8A-%D8%B1%D8%A3%D8%B3-%D8%A7%D9%84%D8%B9%D9%85%D9%84.html') }}">
                                        <img src="{{ default_poster_url() }}" class="img-fluid" alt="img"></a>
                                </div>

                                <!--<div class="blog-content">-->
                                <!--    <div class="blog-title">-->
                                <!--        <h3>-->
                                <!--            <a href="{{ legacy_page('ar/service/مهارات-عامة-موظفين-علي-رأس-العمل.html') }}">-->
                                <!--                مهارات عامة - موظفين علي رأس العمل-->
                                <!--            </a>-->
                                <!--        </h3>-->
                                <!--    </div>-->

                                <!--<div class="blog-content-footer d-flex justify-content-between align-items-center">-->
                                <!--    <p>-->
                                <!--        <span><i class="feather-clock"></i></span>منذ 4 أشهر-->
                                <!--    </p>-->
                                <!--</div>-->

                                <!--    -->

                                <!--    <div class="gigs-card-footer justify-content-start gap-2">-->
                                <!--        <a class="btn btn-primary"-->
                                <!--           href="{{ legacy_page('ar/service/مهارات-عامة-موظفين-علي-رأس-العمل.html') }}">-->
                                <!--            مزيد من التفاصيل-->
                                <!--            <i class="feather-eye pe-2"></i>-->
                                <!--        </a>-->
                                <!--    </div>-->
                                <!--</div>-->
                            </div>
                        </div>
                        <!-- ONLY CHANGE: columns updated to 3 per row -->
                        <div class="col-12 col-md-6">
                            <div class="blog-grid program">
                                <div class="blog-img">
                                    <a
                                        href="{{ legacy_page('ar/service/%D9%85%D9%87%D8%A7%D8%B1%D8%A7%D8%AA-%D9%85%D9%87%D9%86%D9%8A%D8%A9-%D8%A8%D8%A7%D8%AD%D8%AB%D9%8A%D9%86-%D8%B9%D9%86-%D8%B9%D9%85%D9%84.html') }}">
                                        <img src="{{ default_poster_url() }}" class="img-fluid" alt="img"></a>
                                </div>

                                <!--<div class="blog-content">-->
                                <!--    <div class="blog-title">-->
                                <!--        <h3>-->
                                <!--            <a href="{{ legacy_page('ar/service/مهارات-مهنية-باحثين-عن-عمل.html') }}">-->
                                <!--                مهارات مهنية - باحثين عن عمل-->
                                <!--            </a>-->
                                <!--        </h3>-->
                                <!--    </div>-->

                                <!--<div class="blog-content-footer d-flex justify-content-between align-items-center">-->
                                <!--    <p>-->
                                <!--        <span><i class="feather-clock"></i></span>منذ 4 أشهر-->
                                <!--    </p>-->
                                <!--</div>-->

                                <!--    -->

                                <!--    <div class="gigs-card-footer justify-content-start gap-2">-->
                                <!--        <a class="btn btn-primary"-->
                                <!--           href="{{ legacy_page('ar/service/مهارات-مهنية-باحثين-عن-عمل.html') }}">-->
                                <!--            مزيد من التفاصيل-->
                                <!--            <i class="feather-eye pe-2"></i>-->
                                <!--        </a>-->
                                <!--    </div>-->
                                <!--</div>-->
                            </div>
                        </div>
                        <!-- ONLY CHANGE: columns updated to 3 per row -->
                        <div class="col-12 col-md-6">
                            <div class="blog-grid program">
                                <div class="blog-img">
                                    <a
                                        href="{{ legacy_page('ar/service/%D9%85%D9%87%D8%A7%D8%B1%D8%A7%D8%AA-%D9%85%D9%87%D9%86%D9%8A%D8%A9-%D8%A7%D9%84%D9%85%D9%88%D8%B8%D9%81%D9%8A%D9%86-%D8%B9%D9%84%D9%8A-%D8%B1%D8%A3%D8%B3-%D8%A7%D9%84%D8%B9%D9%85%D9%84.html') }}">
                                        <img src="{{ default_poster_url() }}" class="img-fluid" alt="img"></a>
                                </div>

                                <!--<div class="blog-content">-->
                                <!--    <div class="blog-title">-->
                                <!--        <h3>-->
                                <!--            <a href="{{ legacy_page('ar/service/مهارات-مهنية-الموظفين-علي-رأس-العمل.html') }}">-->
                                <!--                مهارات مهنية - الموظفين علي رأس العمل-->
                                <!--            </a>-->
                                <!--        </h3>-->
                                <!--    </div>-->

                                <!--<div class="blog-content-footer d-flex justify-content-between align-items-center">-->
                                <!--    <p>-->
                                <!--        <span><i class="feather-clock"></i></span>منذ 4 أشهر-->
                                <!--    </p>-->
                                <!--</div>-->

                                <!--    -->

                                <!--    <div class="gigs-card-footer justify-content-start gap-2">-->
                                <!--        <a class="btn btn-primary"-->
                                <!--           href="{{ legacy_page('ar/service/مهارات-مهنية-الموظفين-علي-رأس-العمل.html') }}">-->
                                <!--            مزيد من التفاصيل-->
                                <!--            <i class="feather-eye pe-2"></i>-->
                                <!--        </a>-->
                                <!--    </div>-->
                                <!--</div>-->
                            </div>
                        </div>

                        <div class="w-100 text-center mt-4">
                            <a href="{{ legacy_page('ar/services.html') }}" class="btn btn-primary">
                                جميع البرامج </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- /Explore Gigs -->


        <!-- partners -->
        <div class="client-slider-sec">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-header aos aos-init aos-animate" data-aos="fade-up">

                            <h2>شركاء النجاح</h2>
                        </div>
                        <div class="clients-slider owl-carousel owl-rtl owl-loaded owl-drag">


                            <div class="owl-stage-outer">
                                <div class="owl-stage"
                                    style="transform: translate3d(0px, 0px, 0px); transition: all; width: 1980px;">
                                    <div class="owl-item active" style="width: 636px; margin-left: 24px;">
                                        <div class="client-logo">
                                            <img src="{{ static_asset('assets/1853384885027491.png') }}" alt="">
                                        </div>
                                    </div>
                                    <div class="owl-item active" style="width: 636px; margin-left: 24px;">
                                        <div class="client-logo">
                                            <img src="{{ static_asset('assets/1853385108613939.png') }}" alt="">
                                        </div>
                                    </div>
                                    <div class="owl-item" style="width: 636px; margin-left: 24px;">
                                        <div class="client-logo">
                                            <img src="{{ static_asset('assets/1853384983114238.png') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><i
                                        class="fa-solid fa-chevron-left"></i></button><button type="button"
                                    role="presentation" class="owl-next"><i
                                        class="fa-solid fa-chevron-right"></i></button></div>
                            <div class="owl-dots disabled"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /partners -->


        <!-- Clinets -->
        <div class="client-slider-sec">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                            <!--<h2> معتمدون لدى <span class="site_name"> + 2</span></h2>-->
                            <h2> معتمدون لدى</h2>
                        </div>
                        <div class="clients-slider owl-carousel owl-rtl owl-loaded owl-drag">


                            <div class="owl-stage-outer">
                                <div class="owl-stage"
                                    style="transform: translate3d(0px, 0px, 0px); transition: all; width: 1320px;">
                                    <div class="owl-item active" style="width: 636px; margin-left: 24px;">
                                        <div class="client-logo">
                                            <img src="{{ static_asset('assets/1857913315552753.png') }}" class="w-auto" alt="">
                                        </div>
                                    </div>
                                    <div class="owl-item active" style="width: 636px; margin-left: 24px;">
                                        <div class="client-logo">
                                            <img src="{{ static_asset('assets/516e9932-3a38-4c92-a79c-99606a4c6dd9.png') }}" class="w-auto"
                                                alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><i
                                        class="fa-solid fa-chevron-left"></i></button><button type="button"
                                    role="presentation" class="owl-next"><i
                                        class="fa-solid fa-chevron-right"></i></button></div>
                            <div class="owl-dots disabled"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Clinets -->


        <!-- Explore Gigs -->
        <section class="explore-gigs-section">
            <div class="container">
                <div class="section-head d-flex">
                    <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                        <h2> الأخبار والفعاليات</h2>
                    </div>
                </div>
                <div class="blog">
                    <div class="row">
                        <div class="col-md-4 col-sm-6">
                            <div class="blog-grid">
                                <div class="blog-img">
                                    <a
                                        href="{{ legacy_page('ar/blog/%D8%AA%D9%83%D8%A7%D9%85%D9%84-%D9%85%D8%B9%D8%B1%D9%81%D9%8A-%D8%A8%D9%8A%D9%86-%D9%85%D8%B9%D9%87%D8%AF-%D8%A7%D9%84%D8%A8%D8%AD%D9%88%D8%AB-%D8%A8%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84-%D9%88%D9%85%D8%A4%D8%B3%D8%B3%D8%A9-%D8%A8%D9%8A%D8%B1%D9%84%D8%B3-%D8%AA%D8%B1%D9%8A%D9%86%D9%8A%D9%86%D8%BA-%D8%A2%D9%86%D8%AF-%D8%AF%D9%8A%D9%81%D9%8A%D9%84%D9%88%D8%A8%D9%85%D9%86%D8%AA-%D9%84%D8%AA%D8%B7%D9%88%D9%8A%D8%B1-%D8%A7%D9%84%D9%82%D8%AF%D8%B1%D8%A7%D8%AA-%D8%A7%D9%84%D8%A8%D8%B4%D8%B1%D9%8A%D8%A9.html') }}">
                                        <img src="{{ default_poster_url() }}" class="img-fluid" alt="img"></a>

                                </div>
                                <div class="blog-content">
                                    <div class="user-head">
                                        <div class="badge-text">
                                            <a href="javascript:void(0);" class="badge bg-primary-light"> الاخبار
                                                والفعاليات </a>
                                        </div>
                                    </div>
                                    <div class="blog-title">
                                        <h3>
                                            <a
                                                href="{{ legacy_page('ar/blog/%D8%AA%D9%83%D8%A7%D9%85%D9%84-%D9%85%D8%B9%D8%B1%D9%81%D9%8A-%D8%A8%D9%8A%D9%86-%D9%85%D8%B9%D9%87%D8%AF-%D8%A7%D9%84%D8%A8%D8%AD%D9%88%D8%AB-%D8%A8%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84-%D9%88%D9%85%D8%A4%D8%B3%D8%B3%D8%A9-%D8%A8%D9%8A%D8%B1%D9%84%D8%B3-%D8%AA%D8%B1%D9%8A%D9%86%D9%8A%D9%86%D8%BA-%D8%A2%D9%86%D8%AF-%D8%AF%D9%8A%D9%81%D9%8A%D9%84%D9%88%D8%A8%D9%85%D9%86%D8%AA-%D9%84%D8%AA%D8%B7%D9%88%D9%8A%D8%B1-%D8%A7%D9%84%D9%82%D8%AF%D8%B1%D8%A7%D8%AA-%D8%A7%D9%84%D8%A8%D8%B4%D8%B1%D9%8A%D8%A9.html') }}">
                                                تكامل معرفي بين الجامعة العربية المفتوحة ومؤسسة بيرلس ترينينغ آند
                                                ديفيلوبمنت لتطوير القدرات البشرية</a>
                                        </h3>
                                    </div>


                                    <div class="gigs-card-footer justify-content-start gap-2">
                                        <a class="btn btn-primary"
                                            href="{{ legacy_page('ar/blog/%D8%AA%D9%83%D8%A7%D9%85%D9%84-%D9%85%D8%B9%D8%B1%D9%81%D9%8A-%D8%A8%D9%8A%D9%86-%D9%85%D8%B9%D9%87%D8%AF-%D8%A7%D9%84%D8%A8%D8%AD%D9%88%D8%AB-%D8%A8%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84-%D9%88%D9%85%D8%A4%D8%B3%D8%B3%D8%A9-%D8%A8%D9%8A%D8%B1%D9%84%D8%B3-%D8%AA%D8%B1%D9%8A%D9%86%D9%8A%D9%86%D8%BA-%D8%A2%D9%86%D8%AF-%D8%AF%D9%8A%D9%81%D9%8A%D9%84%D9%88%D8%A8%D9%85%D9%86%D8%AA-%D9%84%D8%AA%D8%B7%D9%88%D9%8A%D8%B1-%D8%A7%D9%84%D9%82%D8%AF%D8%B1%D8%A7%D8%AA-%D8%A7%D9%84%D8%A8%D8%B4%D8%B1%D9%8A%D8%A9.html') }}">
                                            مزيد من التفاصيل <i class="feather-eye pe-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="blog-grid">
                                <div class="blog-img">
                                    <a
                                        href="{{ legacy_page('ar/blog/%D9%85%D8%B9%D9%87%D8%AF-%D8%A7%D9%84%D8%A8%D8%AD%D9%88%D8%AB-%D9%88%D8%A7%D9%84%D8%AF%D8%B1%D8%A7%D8%B3%D8%A7%D8%AA-%D8%A7%D9%84%D8%A7%D8%B3%D8%AA%D8%B4%D8%A7%D8%B1%D9%8A%D8%A9-%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84-%D8%AA%D8%B7%D9%84%D9%82-%D8%A8%D8%B1%D9%86%D8%A7%D9%85%D8%AC%D8%A7%D9%8B-%D8%AA%D8%AF%D8%B1%D9%8A%D8%A8%D9%8A%D8%A7%D9%8B-%D9%84%D8%B1%D9%81%D8%B9-%D9%83%D9%81%D8%A7%D8%A1%D8%A9-%D8%A7%D9%84%D9%83%D9%88%D8%A7%D8%AF%D8%B1-%D8%A7%D9%84%D9%85%D9%87%D9%86%D9%8A%D8%A9-%D8%A8%D8%B4%D8%B1%D9%83%D8%A9-%D9%82%D8%B2%D8%A7%D8%B2-%D9%84%D9%84%D8%AA%D8%AC%D8%A7%D8%B1%D8%A9-%D8%AD%D8%B3%D9%8A%D9%86-%D8%A8%D9%83%D8%B1%D9%8A-%D9%82%D8%B2%D8%A7%D8%B2-%D9%88%D8%B4%D8%B1%D9%83%D8%A7%D9%87.html') }}">
                                        <img src="{{ default_poster_url() }}" class="img-fluid" alt="img"></a>

                                </div>
                                <div class="blog-content">
                                    <div class="user-head">
                                        <div class="badge-text">
                                            <a href="javascript:void(0);" class="badge bg-primary-light"> الاخبار
                                                والفعاليات </a>
                                        </div>
                                    </div>
                                    <div class="blog-title">
                                        <h3>
                                            <a
                                                href="{{ legacy_page('ar/blog/%D9%85%D8%B9%D9%87%D8%AF-%D8%A7%D9%84%D8%A8%D8%AD%D9%88%D8%AB-%D9%88%D8%A7%D9%84%D8%AF%D8%B1%D8%A7%D8%B3%D8%A7%D8%AA-%D8%A7%D9%84%D8%A7%D8%B3%D8%AA%D8%B4%D8%A7%D8%B1%D9%8A%D8%A9-%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84-%D8%AA%D8%B7%D9%84%D9%82-%D8%A8%D8%B1%D9%86%D8%A7%D9%85%D8%AC%D8%A7%D9%8B-%D8%AA%D8%AF%D8%B1%D9%8A%D8%A8%D9%8A%D8%A7%D9%8B-%D9%84%D8%B1%D9%81%D8%B9-%D9%83%D9%81%D8%A7%D8%A1%D8%A9-%D8%A7%D9%84%D9%83%D9%88%D8%A7%D8%AF%D8%B1-%D8%A7%D9%84%D9%85%D9%87%D9%86%D9%8A%D8%A9-%D8%A8%D8%B4%D8%B1%D9%83%D8%A9-%D9%82%D8%B2%D8%A7%D8%B2-%D9%84%D9%84%D8%AA%D8%AC%D8%A7%D8%B1%D8%A9-%D8%AD%D8%B3%D9%8A%D9%86-%D8%A8%D9%83%D8%B1%D9%8A-%D9%82%D8%B2%D8%A7%D8%B2-%D9%88%D8%B4%D8%B1%D9%83%D8%A7%D9%87.html') }}">
                                                الجامعة العربية المفتوحة تطلق برنامجاً تدريبياً لرفع
                                                كفاءة الكوادر المهنية بشركة قزاز للتجارة حسين بكري قزاز وشركاه</a>
                                        </h3>
                                    </div>


                                    <div class="gigs-card-footer justify-content-start gap-2">
                                        <a class="btn btn-primary"
                                            href="{{ legacy_page('ar/blog/%D9%85%D8%B9%D9%87%D8%AF-%D8%A7%D9%84%D8%A8%D8%AD%D9%88%D8%AB-%D9%88%D8%A7%D9%84%D8%AF%D8%B1%D8%A7%D8%B3%D8%A7%D8%AA-%D8%A7%D9%84%D8%A7%D8%B3%D8%AA%D8%B4%D8%A7%D8%B1%D9%8A%D8%A9-%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84-%D8%AA%D8%B7%D9%84%D9%82-%D8%A8%D8%B1%D9%86%D8%A7%D9%85%D8%AC%D8%A7%D9%8B-%D8%AA%D8%AF%D8%B1%D9%8A%D8%A8%D9%8A%D8%A7%D9%8B-%D9%84%D8%B1%D9%81%D8%B9-%D9%83%D9%81%D8%A7%D8%A1%D8%A9-%D8%A7%D9%84%D9%83%D9%88%D8%A7%D8%AF%D8%B1-%D8%A7%D9%84%D9%85%D9%87%D9%86%D9%8A%D8%A9-%D8%A8%D8%B4%D8%B1%D9%83%D8%A9-%D9%82%D8%B2%D8%A7%D8%B2-%D9%84%D9%84%D8%AA%D8%AC%D8%A7%D8%B1%D8%A9-%D8%AD%D8%B3%D9%8A%D9%86-%D8%A8%D9%83%D8%B1%D9%8A-%D9%82%D8%B2%D8%A7%D8%B2-%D9%88%D8%B4%D8%B1%D9%83%D8%A7%D9%87.html') }}">
                                            مزيد من التفاصيل <i class="feather-eye pe-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="blog-grid">
                                <div class="blog-img">
                                    <a
                                        href="{{ legacy_page('ar/blog/%D8%AA%D8%B9%D8%A7%D9%88%D9%86-%D9%85%D8%B9-%D8%B4%D8%B1%D9%83%D8%A9-%D8%A7%D9%84%D9%85%D8%B4%D8%B1%D9%82-%D9%84%D9%84%D8%AE%D8%AF%D9%85%D8%A7%D8%AA-%D8%A7%D9%84%D9%81%D9%86%D9%8A%D8%A9-%D8%A7%D9%84%D9%85%D8%AD%D8%AF%D9%88%D8%AF%D8%A9-%D8%B6%D9%85%D9%86-%D8%A8%D8%B1%D8%A7%D9%85%D8%AC-%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84.html') }}">
                                        <img src="{{ default_poster_url() }}" class="img-fluid" alt="img"></a>

                                </div>
                                <div class="blog-content">
                                    <div class="user-head">
                                        <div class="badge-text">
                                            <a href="javascript:void(0);" class="badge bg-primary-light"> الاخبار
                                                والفعاليات </a>
                                        </div>
                                    </div>
                                    <div class="blog-title">
                                        <h3>
                                            <a
                                                href="{{ legacy_page('ar/blog/%D8%AA%D8%B9%D8%A7%D9%88%D9%86-%D9%85%D8%B9-%D8%B4%D8%B1%D9%83%D8%A9-%D8%A7%D9%84%D9%85%D8%B4%D8%B1%D9%82-%D9%84%D9%84%D8%AE%D8%AF%D9%85%D8%A7%D8%AA-%D8%A7%D9%84%D9%81%D9%86%D9%8A%D8%A9-%D8%A7%D9%84%D9%85%D8%AD%D8%AF%D9%88%D8%AF%D8%A9-%D8%B6%D9%85%D9%86-%D8%A8%D8%B1%D8%A7%D9%85%D8%AC-%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84.html') }}">
                                                تعاون مع شركة المشرق للخدمات الفنية المحدودة ضمن برامج مركز التعلم المستمر
                                                والدراسات الاستشارية بالجامعة العربية المفتوحة.</a>
                                        </h3>
                                    </div>


                                    <div class="gigs-card-footer justify-content-start gap-2">
                                        <a class="btn btn-primary"
                                            href="{{ legacy_page('ar/blog/%D8%AA%D8%B9%D8%A7%D9%88%D9%86-%D9%85%D8%B9-%D8%B4%D8%B1%D9%83%D8%A9-%D8%A7%D9%84%D9%85%D8%B4%D8%B1%D9%82-%D9%84%D9%84%D8%AE%D8%AF%D9%85%D8%A7%D8%AA-%D8%A7%D9%84%D9%81%D9%86%D9%8A%D8%A9-%D8%A7%D9%84%D9%85%D8%AD%D8%AF%D9%88%D8%AF%D8%A9-%D8%B6%D9%85%D9%86-%D8%A8%D8%B1%D8%A7%D9%85%D8%AC-%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%AD%D8%A7%D8%A6%D9%84.html') }}">
                                            مزيد من التفاصيل <i class="feather-eye pe-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-100 text-center mt-4">
                            <a href="{{ legacy_page('ar/blogs.html') }}" class="btn btn-primary">
                                جميع الأخبار والفعاليات </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- /Explore Gigs -->


        <section class="testimonial-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                            <h2>آراء العملاء </h2>
                        </div>
                        <div class="testimonial-slider owl-carousel owl-rtl owl-loaded owl-drag">


                            <div class="owl-stage-outer">
                                <div class="owl-stage"
                                    style="transform: translate3d(1318px, 0px, 0px); transition: all; width: 4833px;">
                                    <div class="owl-item cloned" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ استفدت كثيرًا من البرامج المقدمة، خاصة في المهارات المهنية، وساعدني ذلك
                                                على الاستعداد لسوق العمل بثقة أكبر ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038435618862.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> محمد السبيعي</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item cloned" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ برنامج مهارات قدم لي تجربة تدريبية متكاملة من حيث المحتوى والتنظيم
                                                والدعم، وأنصح به كل باحث عن عمل ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038521958109.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> سارة المطيري</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item cloned" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ البرنامج ساهم في تطوير مهاراتي وربطني بفرص وظيفية مناسبة، وكان له أثر
                                                إيجابي واضح على مساري المهني ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038589759124.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> خالد الحربي</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item active" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ برنامج مهارات كان تجربة مميزة ساعدتني على تطوير مهاراتي المهنية وربطني
                                                بفرص عملية حقيقية، والتدريب كان منظم وذو محتوى عالي الجودة ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038251279600.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> أحمد العتيبي</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item active" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ التدريب كان عملي ومباشر، والمدربين قدموا محتوى واضح ومفيد يتماشى مع
                                                احتياجات سوق العمل الحالي ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038289987730.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> نورة القحطاني</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item active" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ استفدت كثيرًا من البرامج المقدمة، خاصة في المهارات المهنية، وساعدني ذلك
                                                على الاستعداد لسوق العمل بثقة أكبر ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038435618862.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> محمد السبيعي</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item active" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ برنامج مهارات قدم لي تجربة تدريبية متكاملة من حيث المحتوى والتنظيم
                                                والدعم، وأنصح به كل باحث عن عمل ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038521958109.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> سارة المطيري</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ البرنامج ساهم في تطوير مهاراتي وربطني بفرص وظيفية مناسبة، وكان له أثر
                                                إيجابي واضح على مساري المهني ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038589759124.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> خالد الحربي</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item cloned" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ برنامج مهارات كان تجربة مميزة ساعدتني على تطوير مهاراتي المهنية وربطني
                                                بفرص عملية حقيقية، والتدريب كان منظم وذو محتوى عالي الجودة ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038251279600.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> أحمد العتيبي</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item cloned" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ التدريب كان عملي ومباشر، والمدربين قدموا محتوى واضح ومفيد يتماشى مع
                                                احتياجات سوق العمل الحالي ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038289987730.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> نورة القحطاني</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="owl-item cloned" style="width: 417.333px; margin-left: 22px;">
                                        <div class="testimonial-item aos aos-init aos-animate" data-aos="fade-up">

                                            <h6></h6>
                                            <p>“ استفدت كثيرًا من البرامج المقدمة، خاصة في المهارات المهنية، وساعدني ذلك
                                                على الاستعداد لسوق العمل بثقة أكبر ”</p>
                                            <div class="star-rate">
                                                <span>

                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                    <i class="fa-solid fa-star filled"></i>
                                                </span>
                                            </div>
                                            <div class="testimonial-user">
                                                <img src="{{ static_asset('assets/1853038435618862.png') }}">
                                                <div class="testimonial-info">
                                                    <h6> محمد السبيعي</h6>
                                                    <p> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="owl-nav"><button type="button" role="presentation" class="owl-prev"><i
                                        class="fa-solid fa-chevron-left"></i></button><button type="button"
                                    role="presentation" class="owl-next"><i
                                        class="fa-solid fa-chevron-right"></i></button></div>
                            <div class="owl-dots disabled"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-bg">
                <div class="testimonial-bg1">
                    <img src="{{ static_asset('assets/testimonial-bg-01.png') }}" alt="Shape">
                </div>
                <div class="testimonial-bg2">
                    <img src="{{ static_asset('assets/testimonial-bg-02.png') }}" alt="Shape">
                </div>
                <div class="testimonial-bg3">
                    <img src="{{ static_asset('assets/testimonial-bg-03.png') }}" alt="Shape">
                </div>
            </div>
        </section>


        <section class="counterSec">
            <div class="container">
                <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                    <h2> الأسئلة الشائعة </h2>
                </div>
                <div class="chapter-accordion accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button " type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq0">
                                <i class="fas fa-question-circle mx-2"></i>
                                ما هي منصة مركز التعلم المستمر؟
                            </button>
                        </h2>
                        <div id="faq0" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                منصة مركز التعلم المستمر هي منصة تعليمية رقمية تقدم برامج تعليمية وتدريبية احترافية مرنة تهدف إلى
                                تطوير المهارات وتعزيز المعرفة بما يتوافق مع احتياجات سوق العمل.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq1">
                                <i class="fas fa-question-circle mx-2"></i>
                                من هم المستفيدون من منصة مركز التعلم المستمر؟
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse ">
                            <div class="accordion-body">
                                تستهدف المنصة الأفراد والطلاب والموظفين والقيادات، بالإضافة إلى الجهات الحكومية والخاصة
                                الراغبة في تطوير مهارات كوادرها ورفع كفاءتها المهنية.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq2">
                                <i class="fas fa-question-circle mx-2"></i>
                                هل تقدم المنصة برامج تعليمية حضورية وعن بُعد؟
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse ">
                            <div class="accordion-body">
                                تقدم منصة مركز التعلم المستمر برامج تعليمية وتدريبية عبر التعلم الإلكتروني عن بُعد، مع إمكانية تقديم
                                برامج حضورية أو مدمجة حسب طبيعة البرنامج.، مع الالتزام بمعايير الجودة في المحتوى
                                والتقديم.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq3">
                                <i class="fas fa-question-circle mx-2"></i>
                                كيف يمكن التسجيل في منصة مركز التعلم المستمر؟
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse ">
                            <div class="accordion-body">
                                يمكن التسجيل في البرامج من خلال الموقع الإلكتروني للمنصة أو عبر قنوات التواصل الرسمية
                                المتاحة.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section class="counterSec">

            <div class="container">
                <div class="section-header aos aos-init aos-animate" data-aos="fade-up">
                    <h2> <span class="site_name"> منصة مركز التعلم المستمر </span> في أرقام </h2>
                </div>
                <div class="counter-wrap">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6">
                            <div class="counter-item mb-4">
                                <h6 class="mb-1 d-flex align-items-center justify-content-center "><i
                                        class="isax isax-global5 me-2"></i> عدد المتدربين </h6>
                                <h3 class="display-6"><span class="counter animated fadeInDownBig">1000</span>+
                                </h3>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="counter-item mb-4">
                                <h6 class="mb-1 d-flex align-items-center justify-content-center "><i
                                        class="isax isax-global5 me-2"></i> عدد المدربين </h6>
                                <h3 class="display-6"><span class="counter animated fadeInDownBig">43</span>+
                                </h3>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="counter-item mb-4">
                                <h6 class="mb-1 d-flex align-items-center justify-content-center "><i
                                        class="isax isax-global5 me-2"></i> عدد الدورات التدريبية </h6>
                                <h3 class="display-6"><span class="counter animated fadeInDownBig">63</span>+
                                </h3>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="counter-item mb-4">
                                <h6 class="mb-1 d-flex align-items-center justify-content-center "><i
                                        class="isax isax-global5 me-2"></i> معدل الرضا </h6>
                                <h3 class="display-6"><span class="counter animated fadeInDownBig">99</span>+
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- Expert Section -->
        <section class="popular-section expert-section">
            <div class="container">
                <div class="expert-header">
                    <div class="section-header text-center aos aos-init aos-animate" data-aos="fade-up">
                        <h2><span> مميزات منصة مركز التعلم المستمر </span>

                        </h2>
                    </div>
                </div>
                <div class="expert-wrapper">
                    <div class="row gx-0 justify-content-center">
                        <div class="col-lg-4 col-md-6 aos aos-init aos-animate" data-aos="fade-up">
                            <div class="expert-item">
                                <div class="expert-icon">
                                    <img src="{{ static_asset('assets/1853132469069541.png') }}" alt="صورة">
                                </div>
                                <div class="expert-info">
                                    <h4>شهادات معتمدة</h4>
                                    <p></p>
                                    <p dir="rtl" data-start="68" data-end="140">شهادات موثوقة تعزز مسارك المهني وتزيد
                                        فرصك الوظيفية</p>
                                    <p data-start="142" data-end="207">&nbsp;</p>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 aos aos-init aos-animate" data-aos="fade-up">
                            <div class="expert-item">
                                <div class="expert-icon">
                                    <img src="{{ static_asset('assets/1853132752703292.png') }}" alt="صورة">
                                </div>
                                <div class="expert-info">
                                    <h4>عشرات التخصصات</h4>
                                    <p></p>
                                    <p dir="rtl" data-start="142" data-end="207">برامج تدريبية متنوعة تلبي متطلبات سوق
                                        العمل</p>
                                    <p data-start="209" data-end="272">&nbsp;</p>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 aos aos-init aos-animate" data-aos="fade-up">
                            <div class="expert-item">
                                <div class="expert-icon">
                                    <img src="{{ static_asset('assets/1853133034226589.png') }}" alt="صورة">
                                </div>
                                <div class="expert-info">
                                    <h4>مدربون احترافيون</h4>
                                    <p></p>
                                    <p data-start="209" data-end="272">خبراء معتمدون بخبرة عملية محلية وعالمية</p>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 aos aos-init aos-animate" data-aos="fade-up">
                            <div class="expert-item">
                                <div class="expert-icon">
                                    <img src="{{ static_asset('assets/1853133514491196.png') }}" alt="صورة">
                                </div>
                                <div class="expert-info">
                                    <h4>سهولة الاستخدام</h4>
                                    <p></p>
                                    <p dir="rtl" data-start="274" data-end="342">تعلم مرن عبر منصة إلكترونية سهلة مع دعم
                                        مستمر</p>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 aos aos-init aos-animate" data-aos="fade-up">
                            <div class="expert-item">
                                <div class="expert-icon">
                                    <img src="{{ static_asset('assets/1853382791824256.png') }}" alt="صورة">
                                </div>
                                <div class="expert-info">
                                    <h4>التطبيق العملي المباشر</h4>
                                    <p></p>
                                    <p dir="rtl">تجربة تدريبية تركز على التطبيق والنتائج</p>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <a href="tel:+966543406744" class="float-call">
            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path
                        d="M21.97 18.33C21.97 18.69 21.89 19.06 21.72 19.42C21.55 19.78 21.33 20.12 21.04 20.44C20.55 20.98 20.01 21.37 19.4 21.62C18.8 21.87 18.15 22 17.45 22C16.43 22 15.34 21.76 14.19 21.27C13.04 20.78 11.89 20.12 10.75 19.29C9.6 18.45 8.51 17.52 7.47 16.49C6.44 15.45 5.51 14.36 4.68 13.22C3.86 12.08 3.2 10.94 2.72 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C11.27 13.75 11.79 14.24 12.32 14.69C12.84 15.13 13.27 15.43 13.61 15.61C13.66 15.63 13.72 15.66 13.79 15.69C13.87 15.72 13.95 15.73 14.04 15.73C14.21 15.73 14.34 15.67 14.45 15.56L15.21 14.81C15.46 14.56 15.7 14.37 15.93 14.25C16.16 14.11 16.39 14.04 16.64 14.04C16.83 14.04 17.03 14.08 17.25 14.17C17.47 14.26 17.7 14.39 17.95 14.56L21.26 16.91C21.52 17.09 21.7 17.3 21.81 17.55C21.91 17.8 21.97 18.05 21.97 18.33Z"
                        stroke="#ffffff" stroke-width="1.5" stroke-miterlimit="10"></path>
                </g>
            </svg>
        </a>
